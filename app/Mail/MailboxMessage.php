<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class MailboxMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{path: string, name: string, mime: string, size: int}>  $storedAttachments
     */
    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public array $storedAttachments = [],
        public ?string $inReplyTo = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('services.hostinger_mailbox.address'),
                config('mail.from.name'),
            ),
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'mail.mailbox-message',
            text: 'mail.mailbox-message-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return array_map(
            fn (array $attachment): Attachment => Attachment::fromStorageDisk('local', $attachment['path'])
                ->as($attachment['name'])
                ->withMime($attachment['mime']),
            $this->storedAttachments,
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            references: $this->inReplyTo ? [$this->inReplyTo] : [],
            text: $this->inReplyTo ? ['In-Reply-To' => $this->inReplyTo] : [],
        );
    }
}
