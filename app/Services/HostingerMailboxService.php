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
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

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
     * Synchronize messages from the mailbox's IMAP inbox.
     *
     * A null day range only fetches UIDs newer than the last synchronized
     * message. Passing a day range is reserved for an explicit backfill.
     */
    public function sync(?int $days = null): int
    {
        $config = config('services.hostinger_mailbox');

        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new RuntimeException('Faltan las credenciales IMAP de Hostinger en el entorno.');
        }

        $client = (new ClientManager)->make([
            'host' => $config['host'],
            'port' => $config['port'],
            'encryption' => $config['encryption'] ?? 'ssl',
            'validate_cert' => $config['validate_cert'] ?? true,
            'username' => $username,
            'password' => $password,
            'protocol' => 'imap',
        ]);

        try {
            $client->connect();
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo conectar con el buzón IMAP de Hostinger: '.$exception->getMessage());
        }

        try {
            $folder = $client->getFolder((string) ($config['folder'] ?? 'INBOX'));

            if (! $folder) {
                throw new RuntimeException('No se encontró la carpeta configurada en el buzón IMAP.');
            }

            $lastUid = (int) (MailMessage::query()->max('mailbox_uid') ?? 0);
            $query = $folder->query()->leaveUnread();

            $messages = match (true) {
                $days !== null => $query->whereSince(now()->subDays($days))->get(),
                $lastUid > 0 => $query->getByUidGreaterOrEqual($lastUid + 1),
                default => $query->whereAll()->get(),
            };

            $synchronized = 0;

            foreach ($messages->sortBy(fn (Message $message): int => (int) $message->uid) as $message) {
                $this->synchronizeMessage($message, $config);
                $synchronized++;
            }

            return $synchronized;
        } finally {
            $client->disconnect();
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function synchronizeMessage(Message $message, array $config): void
    {
        $uid = (int) $message->uid;
        $messageId = $this->attributeString($message->message_id)
            ?: sprintf('<webklex-%d@%s>', $uid, $config['host']);
        $sender = $message->from?->first();
        $senderEmail = $sender?->mail ? strtolower($sender->mail) : '';

        $record = MailMessage::query()
            ->where('mailbox_uid', $uid)
            ->first()
            ?? MailMessage::query()->where('message_id', $messageId)->first()
            ?? new MailMessage;
        $isNew = ! $record->exists;

        $record->fill([
            'message_id' => $messageId,
            'mailbox_uid' => $uid,
            'direction' => 'incoming',
            'thread_id' => $this->attributeString($message->references)
                ?: $this->attributeString($message->in_reply_to)
                    ?: $messageId,
            'in_reply_to' => $this->attributeString($message->in_reply_to) ?: null,
            'from_name' => $sender?->personal ? $this->decodeMime($sender->personal) : null,
            'from_email' => $senderEmail ?: 'desconocido@hostinger.invalid',
            'to_email' => $config['to_email'],
            'subject' => $this->attributeString($message->subject),
            'body_text' => $this->textBody($message),
            'body_html' => $message->hasHTMLBody() ? $message->getHTMLBody() : null,
            'attachments' => $this->storeAttachments($message),
            'received_at' => $this->receivedAt($message),
            'synced_at' => now(),
        ]);

        if ($isNew) {
            $record->is_read = false;
            $record->estado = 'Nuevo';
        }

        $record->save();
    }

    private function textBody(Message $message): string
    {
        if ($message->hasTextBody()) {
            return trim($message->getTextBody());
        }

        if ($message->hasHTMLBody()) {
            return trim(strip_tags($message->getHTMLBody()));
        }

        return '';
    }

    /**
     * @return array<int, array{path: string, name: string, mime: string, size: int}>
     */
    private function storeAttachments(Message $message): array
    {
        $attachments = [];

        foreach ($message->getAttachments() as $attachment) {
            $name = $attachment->name ?: 'adjunto';
            $path = 'mail-attachments/'.Str::uuid().'-'.Str::slug(pathinfo($name, PATHINFO_FILENAME));
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            if ($extension !== '') {
                $path .= '.'.strtolower($extension);
            }

            Storage::disk('local')->put($path, (string) $attachment->content);

            $attachments[] = [
                'path' => $path,
                'name' => $name,
                'mime' => $attachment->getMimeType() ?: 'application/octet-stream',
                'size' => (int) ($attachment->size ?: strlen((string) $attachment->content)),
            ];
        }

        return $attachments;
    }

    private function attributeString(mixed $attribute): string
    {
        return $this->decodeMime(trim((string) $attribute));
    }

    /**
     * Decode RFC 2047 encoded-words (=?UTF-8?Q?...?=) left undecoded when
     * the native imap extension (used internally by webklex as a shortcut)
     * is not available.
     */
    private function decodeMime(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return trim((string) mb_decode_mimeheader($value));
    }

    private function receivedAt(Message $message): CarbonImmutable
    {
        try {
            $date = $message->date?->toDate();

            return $date ? CarbonImmutable::parse($date) : now();
        } catch (Throwable $exception) {
            Log::warning('No se pudo interpretar la fecha de un correo del buzón.', [
                'uid' => $message->uid,
            ]);

            return now();
        }
    }
}
