<?php

namespace App\Mail;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TodoCrudMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Todo $todo,
        public User $recipient,
        public string $actionLabel,
        public string $message,
        public string $subjectLine,
        public array $payload = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.todo-crud',
            with: [
                'todo' => $this->todo,
                'recipient' => $this->recipient,
                'actionLabel' => $this->actionLabel,
                'message' => $this->message,
                'payload' => $this->payload,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
