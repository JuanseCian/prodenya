<?php

namespace App\Services;

use App\Mail\MailboxMessage;
use App\Models\MailMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class HostingerMailboxService
{
    /**
     * Send an outgoing message through Hostinger and retain it in the mailbox.
     *
     * @param  array<int, array{path: string, name: string, mime: string, size: int}>  $attachments
     */
    public function send(
        string $toEmail,
        string $subject,
        string $body,
        array $attachments = [],
        ?MailMessage $replyTo = null,
    ): MailMessage {
        Mail::mailer('hostinger')
            ->to($toEmail)
            ->send(new MailboxMessage($subject, $body, $attachments, $replyTo?->message_id));

        return MailMessage::create([
            'message_id' => '<'.Str::uuid().'@prodenya.local>',
            'direction' => 'outgoing',
            'thread_id' => $replyTo?->thread_id ?: $replyTo?->message_id,
            'in_reply_to' => $replyTo?->message_id,
            'from_name' => config('mail.from.name'),
            'from_email' => config('services.hostinger_mailbox.address'),
            'to_email' => $toEmail,
            'subject' => $subject,
            'body_text' => $body,
            'body_html' => null,
            'sent_at' => now(),
            'received_at' => null,
            'synced_at' => now(),
            'is_read' => true,
            'estado' => 'Respondido',
            'attachments' => $attachments,
        ]);
    }

    /**
     * Synchronize messages from Hostinger's IMAP inbox.
     *
     * A null day range only fetches UIDs newer than the last synchronized
     * message. Passing a day range is reserved for an explicit backfill.
     */
    public function sync(?int $days = null): int
    {
        $config = config('services.hostinger_mailbox');

        if (! extension_loaded('imap') || ! function_exists('imap_open')) {
            throw new RuntimeException('La extensión IMAP de PHP no está instalada.');
        }

        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new RuntimeException('Faltan las credenciales IMAP de Hostinger en el entorno.');
        }

        $stream = @imap_open(
            $this->mailboxPath($config),
            $username,
            $password,
            OP_READONLY,
            1,
            ['DISABLE_AUTHENTICATOR' => 'GSSAPI'],
        );

        if ($stream === false) {
            throw new RuntimeException('No se pudo conectar con el buzón IMAP de Hostinger: '.$this->imapError());
        }

        try {
            $lastUid = (int) (MailMessage::query()->max('mailbox_uid') ?? 0);
            $criteria = $days !== null
                ? 'SINCE "'.now()->subDays($days)->format('d-M-Y').'"'
                : ($lastUid > 0 ? 'UID '.($lastUid + 1).':*' : 'ALL');
            $uids = imap_search($stream, $criteria, SE_UID) ?: [];
            sort($uids, SORT_NUMERIC);
            $synchronized = 0;

            foreach ($uids as $uid) {
                $this->synchronizeMessage($stream, (int) $uid, $config);
                $synchronized++;
            }

            return $synchronized;
        } finally {
            imap_close($stream);
        }
    }

    /**
     * @param  resource  $stream
     * @param  array<string, mixed>  $config
     */
    private function synchronizeMessage($stream, int $uid, array $config): void
    {
        $overview = imap_fetch_overview($stream, (string) $uid, FT_UID)[0] ?? null;

        if (! $overview) {
            return;
        }

        $structure = imap_fetchstructure($stream, $uid, FT_UID);
        [$bodyText, $bodyHtml, $attachments] = $structure
            ? $this->extractBodies($stream, $uid, $structure)
            : ['', null, []];
        $messageId = $this->decodeHeader($overview->message_id ?? '')
            ?: sprintf('<hostinger-%d@%s>', $uid, $config['host']);
        $sender = $this->sender($overview->from ?? '');
        $message = MailMessage::query()
            ->where('mailbox_uid', $uid)
            ->first()
            ?? MailMessage::query()->where('message_id', $messageId)->first()
            ?? new MailMessage;
        $isNew = ! $message->exists;

        $message->fill([
            'message_id' => $messageId,
            'mailbox_uid' => $uid,
            'direction' => 'incoming',
            'thread_id' => $this->decodeHeader($overview->references ?? '')
                ?: $this->decodeHeader($overview->in_reply_to ?? '')
                    ?: $messageId,
            'in_reply_to' => $this->decodeHeader($overview->in_reply_to ?? '') ?: null,
            'from_name' => $sender['name'],
            'from_email' => $sender['email'] ?: 'desconocido@hostinger.invalid',
            'to_email' => $config['to_email'],
            'subject' => $this->decodeHeader($overview->subject ?? ''),
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
            'attachments' => $attachments,
            'received_at' => $this->messageDate($overview->date ?? null),
            'synced_at' => now(),
        ]);

        if ($isNew) {
            $message->is_read = false;
            $message->estado = 'Nuevo';
        }

        $message->save();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function mailboxPath(array $config): string
    {
        $encryption = strtolower((string) ($config['encryption'] ?? 'ssl'));
        $flags = '/imap';

        if ($encryption === 'ssl') {
            $flags .= '/ssl';
        } elseif ($encryption === 'tls') {
            $flags .= '/tls';
        }

        if (! ($config['validate_cert'] ?? true)) {
            $flags .= '/novalidate-cert';
        }

        return sprintf(
            '{%s:%d%s}%s',
            $config['host'],
            $config['port'],
            $flags,
            $config['folder'],
        );
    }

    /**
     * @param  resource  $stream
     * @return array{0: string, 1: string|null, 2: array<int, array{path: string, name: string, mime: string, size: int}>}
     */
    private function extractBodies($stream, int $uid, object $structure): array
    {
        $parts = $this->extractParts($stream, $uid, $structure);
        $bodyText = trim($parts['text']);
        $bodyHtml = $parts['html'] !== '' ? $parts['html'] : null;

        if ($bodyText === '' && $bodyHtml !== null) {
            $bodyText = trim(strip_tags($bodyHtml));
        }

        return [$bodyText, $bodyHtml, $parts['attachments']];
    }

    /**
     * @param  resource  $stream
     * @return array{text: string, html: string, attachments: array<int, array{path: string, name: string, mime: string, size: int}>}
     */
    private function extractParts($stream, int $uid, object $part, string $section = ''): array
    {
        if (isset($part->parts) && is_array($part->parts)) {
            $bodies = ['text' => '', 'html' => '', 'attachments' => []];

            foreach (array_keys($part->parts) as $index) {
                $partSection = $section === '' ? (string) ((int) $index + 1) : $section.'.'.((int) $index + 1);
                $nested = $this->extractParts($stream, $uid, $part->parts[$index], $partSection);

                if ($bodies['text'] === '' && $nested['text'] !== '') {
                    $bodies['text'] = $nested['text'];
                }
                if ($bodies['html'] === '' && $nested['html'] !== '') {
                    $bodies['html'] = $nested['html'];
                }

                $bodies['attachments'] = [...$bodies['attachments'], ...$nested['attachments']];
            }

            return $bodies;
        }

        $body = imap_fetchbody($stream, $uid, $section === '' ? '1' : $section, FT_UID);
        $body = $this->decodeBody($body, (int) ($part->encoding ?? 0));

        $filename = $this->partFilename($part);
        if ($filename !== '') {
            $path = 'mail-attachments/'.Str::uuid().'-'.Str::slug(pathinfo($filename, PATHINFO_FILENAME));
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if ($extension !== '') {
                $path .= '.'.strtolower($extension);
            }

            Storage::disk('local')->put($path, $body);

            return [
                'text' => '',
                'html' => '',
                'attachments' => [[
                    'path' => $path,
                    'name' => $filename,
                    'mime' => $this->partMime($part),
                    'size' => strlen($body),
                ]],
            ];
        }

        $body = $this->convertCharset($body, $this->partCharset($part));

        if ((int) ($part->type ?? -1) !== 0) {
            return ['text' => '', 'html' => '', 'attachments' => []];
        }

        return [
            'text' => strtolower((string) ($part->subtype ?? '')) === 'plain' ? $body : '',
            'html' => strtolower((string) ($part->subtype ?? '')) === 'html' ? $body : '',
            'attachments' => [],
        ];
    }

    private function partFilename(object $part): string
    {
        foreach (array_merge($part->parameters ?? [], $part->dparameters ?? []) as $parameter) {
            if (in_array(strtolower((string) ($parameter->attribute ?? '')), ['filename', 'name'], true)) {
                return $this->decodeHeader((string) ($parameter->value ?? ''));
            }
        }

        return '';
    }

    private function partMime(object $part): string
    {
        $types = [
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'application',
        ];

        return ($types[(int) ($part->type ?? 7)] ?? 'application').'/'.strtolower((string) ($part->subtype ?? 'octet-stream'));
    }

    private function decodeBody(string $body, int $encoding): string
    {
        return match ($encoding) {
            3 => base64_decode($body, true) ?: '',
            4 => quoted_printable_decode($body),
            default => $body,
        };
    }

    private function partCharset(object $part): ?string
    {
        foreach (array_merge($part->parameters ?? [], $part->dparameters ?? []) as $parameter) {
            if (strtolower((string) ($parameter->attribute ?? '')) === 'charset') {
                return (string) ($parameter->value ?? '');
            }
        }

        return null;
    }

    private function convertCharset(string $body, ?string $charset): string
    {
        if ($body === '' || ! $charset || strtoupper($charset) === 'UTF-8') {
            return $body;
        }

        return mb_convert_encoding($body, 'UTF-8', $charset);
    }

    /**
     * @return array{name: string|null, email: string}
     */
    private function sender(string $rawSender): array
    {
        $addresses = imap_rfc822_parse_adrlist($rawSender, '');
        $address = $addresses[0] ?? null;

        if (! $address) {
            return ['name' => null, 'email' => ''];
        }

        $email = sprintf('%s@%s', $address->mailbox ?? '', $address->host ?? '');

        return [
            'name' => $this->decodeHeader($address->personal ?? '') ?: null,
            'email' => strtolower($email),
        ];
    }

    private function decodeHeader(?string $value): string
    {
        return trim($value ? imap_utf8($value) : '');
    }

    private function messageDate(?string $value): CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::parse($value) : now();
        } catch (Throwable) {
            Log::warning('No se pudo interpretar la fecha de un correo de Hostinger.', [
                'date' => $value,
            ]);

            return now();
        }
    }

    private function imapError(): string
    {
        return trim((string) (imap_last_error() ?: 'error desconocido'));
    }
}
