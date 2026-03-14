<?php

namespace App\Mail;

use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TodoReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $todo;
    public $occurrenceDate;

    public function __construct(Todo $todo, Carbon $occurrenceDate)
    {
        $this->todo = $todo;
        $this->occurrenceDate = $occurrenceDate;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Todo Reminder: ' . $this->todo->title,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.todo-reminder',
            with: [
                'todo' => $this->todo,
                'occurrenceDate' => $this->occurrenceDate,
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}
