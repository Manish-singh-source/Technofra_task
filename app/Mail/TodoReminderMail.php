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
    public string $reminderWindow;
    public string $reminderWindowLabel;

    public function __construct(Todo $todo, Carbon $occurrenceDate, string $reminderWindow = 'day_of_6am')
    {
        $this->todo = $todo;
        $this->occurrenceDate = $occurrenceDate;
        $this->reminderWindow = $reminderWindow;
        $this->reminderWindowLabel = $this->resolveReminderWindowLabel($reminderWindow);
    }

    public function envelope()
    {
        return new Envelope(
            subject: sprintf('Todo Reminder (%s): %s', $this->reminderWindowLabel, $this->todo->title),
        );
    }

    public function content()
    {
        return new Content(
                view: 'emails.todo-reminder',
                with: [
                    'todo' => $this->todo,
                    'occurrenceDate' => $this->occurrenceDate,
                    'reminderWindow' => $this->reminderWindow,
                    'reminderWindowLabel' => $this->reminderWindowLabel,
                ],
            );
    }

    public function attachments()
    {
        return [];
    }

    private function resolveReminderWindowLabel(string $reminderWindow): string
    {
        return match ($reminderWindow) {
            'day_before' => '1 Day Before',
            'day_of_6am' => '6 AM',
            'one_hour_before' => '1 Hour Before',
            default => ucfirst(str_replace('_', ' ', $reminderWindow)),
        };
    }
}
