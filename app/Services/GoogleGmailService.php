<?php

namespace App\Services;

use App\Models\GoogleOAuthToken;
use App\Models\MailMessage;
use Carbon\CarbonImmutable;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message as GmailMessage;
use Google\Service\Gmail\MessagePart;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

class GoogleGmailService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.google_gmail.enabled');
    }

    public function isConfigured(): bool
    {
        return filled(config('services.google_gmail.client_id'))
            && filled(config('services.google_gmail.client_secret'))
            && filled(config('services.google_gmail.redirect_uri'));
    }

    public function isConnected(): bool
    {
        return GoogleOAuthToken::query()->exists();
    }

    public function connectedEmail(): ?string
    {
        return GoogleOAuthToken::query()->value('email');
    }

    public function hasSendAccess(): bool
    {
        $scopes = GoogleOAuthToken::query()->value('scopes');

        if (is_string($scopes)) {
            $scopes = json_decode($scopes, true);
        }

        return in_array('https://www.googleapis.com/auth/gmail.send', (array) $scopes, true);
    }

    public function authorizationUrl(string $state): string
    {
        $client = $this->client();
        $client->setState($state);

        return $client->createAuthUrl(config('services.google_gmail.scopes'));
    }

    public function authorize(string $code): GoogleOAuthToken
    {
        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new RuntimeException('Google rechazó la autorización: '.($token['error_description'] ?? $token['error']));
        }

        $client->setAccessToken($token);
        $profile = (new Gmail($client))->users->getProfile('me');
        $email = strtolower((string) $profile->getEmailAddress());
        $refreshToken = (string) ($token['refresh_token'] ?? '');
        $existingToken = GoogleOAuthToken::query()->where('email', $email)->first();

        if ($refreshToken === '' && $existingToken) {
            $refreshToken = $existingToken->refresh_token;
        }

        if ($refreshToken === '') {
            throw new RuntimeException('Google no devolvió un refresh token. Revocá el acceso anterior y volvé a autorizar la cuenta.');
        }

        return GoogleOAuthToken::query()->updateOrCreate(
            ['email' => $email],
            [
                'refresh_token' => $refreshToken,
                'scopes' => is_array($token['scope'] ?? null)
                    ? $token['scope']
                    : preg_split('/\s+/', (string) ($token['scope'] ?? ''), -1, PREG_SPLIT_NO_EMPTY),
                'authorized_at' => now(),
            ],
        );
    }

    public function disconnect(): void
    {
        GoogleOAuthToken::query()->delete();
    }

    /**
     * Send a message through the authorized Gmail account.
     *
     * @param  array<int, array{path: string, name: string, mime: string, size: int}>  $attachments
     */
    public function send(
        string $toEmail,
        string $subject,
        string $body,
        array $attachments = [],
        ?MailMessage $replyTo = null,
        ?string $ccEmail = null,
        ?string $bccEmail = null,
    ): MailMessage {
        if (! $this->hasSendAccess()) {
            throw new RuntimeException('La cuenta de Gmail debe volver a autorizarse para permitir envíos.');
        }

        $client = $this->authenticatedClient();
        $fromEmail = (string) config('services.google_gmail.address');
        $messageId = '<'.Str::uuid().'@'.Str::after($fromEmail, '@').'>';
        $email = (new Email)
            ->from(new Address($fromEmail, (string) config('mail.from.name')))
            ->to($toEmail)
            ->subject($subject)
            ->text($body)
            ->html(nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));

        $email->getHeaders()->addIdHeader('Message-ID', trim($messageId, '<>'));

        if ($replyTo?->message_id) {
            $email->getHeaders()->addTextHeader('In-Reply-To', $replyTo->message_id);
            $email->getHeaders()->addTextHeader('References', $replyTo->message_id);
        }

        if ($ccEmail) {
            $email->cc($ccEmail);
        }

        if ($bccEmail) {
            $email->bcc($bccEmail);
        }

        foreach ($attachments as $attachment) {
            $email->attachFromPath(
                Storage::disk('local')->path($attachment['path']),
                $attachment['name'],
                $attachment['mime'],
            );
        }

        $raw = rtrim(strtr(base64_encode($email->toString()), '+/', '-_'), '=');
        $sent = (new Gmail($client))->users_messages->send('me', new GmailMessage([
            'raw' => $raw,
            'threadId' => $replyTo?->thread_id,
        ]));

        return MailMessage::create([
            'message_id' => $messageId,
            'provider' => 'google',
            'provider_message_id' => $sent->getId(),
            'folder' => 'sent',
            'direction' => 'outgoing',
            'thread_id' => $sent->getThreadId() ?: $replyTo?->thread_id,
            'in_reply_to' => $replyTo?->message_id,
            'from_name' => config('mail.from.name'),
            'from_email' => $fromEmail,
            'to_email' => $toEmail,
            'cc_email' => $ccEmail,
            'bcc_email' => $bccEmail,
            'subject' => $subject,
            'body_text' => $body,
            'body_html' => null,
            'sent_at' => now(),
            'received_at' => null,
            'synced_at' => now(),
            'is_read' => true,
            'preview' => str($body)->squish()->limit(180)->toString(),
            'has_attachments' => $attachments !== [],
            'estado' => 'Respondido',
            'attachments' => $attachments,
        ]);
    }

    public function sync(): int
    {
        $storedToken = GoogleOAuthToken::query()->latest('authorized_at')->first();

        if (! $storedToken) {
            throw new RuntimeException('La cuenta de Gmail todavía no está vinculada.');
        }

        $client = $this->authenticatedClient($storedToken);
        $gmail = new Gmail($client);
        try {
            $messages = $gmail->users_messages->listUsersMessages('me', [
                'q' => 'in:inbox',
                'maxResults' => 100,
            ]);
            $synchronized = 0;

            foreach ($messages->getMessages() ?? [] as $summary) {
                $message = $gmail->users_messages->get('me', $summary->getId(), [
                    'format' => 'full',
                ]);

                $this->synchronizeMessage($message);
                $synchronized++;
            }

            $storedToken->update([
                'history_id' => (string) (new Gmail($client))->users->getProfile('me')->getHistoryId(),
                'last_synced_at' => now(),
                'last_sync_count' => $synchronized,
                'last_sync_error' => null,
            ]);

            return $synchronized;
        } catch (Throwable $exception) {
            $storedToken->update(['last_sync_error' => $exception->getMessage()]);

            throw $exception;
        }
    }

    private function authenticatedClient(?GoogleOAuthToken $storedToken = null): Client
    {
        $storedToken ??= GoogleOAuthToken::query()->latest('authorized_at')->first();

        if (! $storedToken) {
            throw new RuntimeException('La cuenta de Gmail todavía no está vinculada.');
        }

        $client = $this->client();
        $accessToken = $client->fetchAccessTokenWithRefreshToken($storedToken->refresh_token);

        if (isset($accessToken['error'])) {
            throw new RuntimeException('La autorización de Gmail venció. Volvé a vincular la cuenta.');
        }

        $client->setAccessToken($accessToken);

        return $client;
    }

    private function client(): Client
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Faltan GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET o GOOGLE_GMAIL_REDIRECT_URI en el entorno.');
        }

        $client = new Client;
        $client->setApplicationName((string) config('app.name'));
        $client->setClientId((string) config('services.google_gmail.client_id'));
        $client->setClientSecret((string) config('services.google_gmail.client_secret'));
        $client->setRedirectUri((string) config('services.google_gmail.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->setScopes(config('services.google_gmail.scopes'));

        return $client;
    }

    private function synchronizeMessage(GmailMessage $message): void
    {
        $headers = $this->headers($message);
        $messageId = $headers['message-id'] ?? 'gmail:'.$message->getId();
        $bodies = $this->bodies($message->getPayload());
        $bodyHtml = $bodies['html'] ?? null;
        $bodyText = $bodies['text'] ?? ($bodyHtml ? trim(strip_tags($bodyHtml)) : null);
        $labels = $message->getLabelIds() ?? [];
        $folder = in_array('SPAM', $labels, true)
            ? 'spam'
            : (in_array('TRASH', $labels, true) ? 'trash' : (in_array('INBOX', $labels, true) ? 'inbox' : 'archive'));
        $record = MailMessage::query()->firstOrNew(['message_id' => $messageId]);
        $isNew = ! $record->exists;

        $record->fill([
            'provider' => 'google',
            'provider_message_id' => $message->getId(),
            'folder' => $folder,
            'direction' => 'incoming',
            'thread_id' => $message->getThreadId(),
            'from_name' => $this->addressName($headers['from'] ?? null),
            'from_email' => $this->addressEmail($headers['from'] ?? null) ?? '',
            'to_email' => $this->addressEmail($headers['to'] ?? null),
            'cc_email' => $this->addressEmail($headers['cc'] ?? null),
            'bcc_email' => $this->addressEmail($headers['bcc'] ?? null),
            'subject' => $headers['subject'] ?? '(Sin asunto)',
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
            'preview' => str($bodyText)->squish()->limit(180)->toString(),
            'received_at' => $this->receivedAt($headers['date'] ?? null, $message->getInternalDate()),
            'synced_at' => now(),
            'is_read' => ! in_array('UNREAD', $labels, true),
            'is_starred' => in_array('STARRED', $labels, true),
            'has_attachments' => $this->hasAttachments($message->getPayload()),
            'labels' => $labels,
        ]);

        if ($isNew) {
            $record->estado = 'Nuevo';
        }

        $record->save();
    }

    private function hasAttachments(?MessagePart $part): bool
    {
        if (! $part) {
            return false;
        }

        if (filled($part->getFilename())) {
            return true;
        }

        foreach ($part->getParts() ?? [] as $child) {
            if ($this->hasAttachments($child)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, string> */
    private function headers(GmailMessage $message): array
    {
        $headers = [];

        foreach ($message->getPayload()?->getHeaders() ?? [] as $header) {
            $name = strtolower((string) $header->getName());
            $value = (string) $header->getValue();
            $headers[$name] = function_exists('iconv_mime_decode')
                ? (iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8') ?: $value)
                : $value;
        }

        return $headers;
    }

    /** @return array{text: ?string, html: ?string} */
    private function bodies(?MessagePart $part): array
    {
        if (! $part) {
            return ['text' => null, 'html' => null];
        }

        $mimeType = strtolower((string) $part->getMimeType());
        $data = $part->getBody()?->getData();
        $body = $data ? $this->decode($data) : null;
        $result = [
            'text' => $mimeType === 'text/plain' ? $body : null,
            'html' => $mimeType === 'text/html' ? $body : null,
        ];

        foreach ($part->getParts() ?? [] as $child) {
            $nested = $this->bodies($child);
            $result['text'] ??= $nested['text'];
            $result['html'] ??= $nested['html'];
        }

        return $result;
    }

    private function decode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $data .= str_repeat('=', (4 - strlen($data) % 4) % 4);
        $decoded = base64_decode($data, true);

        return $decoded === false ? '' : $decoded;
    }

    private function addressEmail(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        preg_match('/<([^>]+)>/', $value, $matches);

        return strtolower(trim($matches[1] ?? $value));
    }

    private function addressName(?string $value): ?string
    {
        if (! $value || ! str_contains($value, '<')) {
            return null;
        }

        return trim(trim(Str::before($value, '<')), '" ');
    }

    private function receivedAt(?string $date, int|string|null $internalDate): CarbonImmutable
    {
        if ($date) {
            return CarbonImmutable::parse($date);
        }

        return CarbonImmutable::createFromTimestamp((int) floor(($internalDate ?? 0) / 1000));
    }
}
