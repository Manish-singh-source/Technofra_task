<?php

namespace App\Mail;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CalendarEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public string $notificationType;
    public string $subjectLine;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\CalendarEvent $event
     * @return void
     */
    public function __construct(CalendarEvent $event, string $notificationType = 'reminder', ?string $subjectLine = null)
    {
        $this->event = $event;
        $this->notificationType = $notificationType;
        $this->subjectLine = $subjectLine ?: $this->defaultSubject($notificationType);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.calendar-event',
            with: [
                'event' => $this->event,
                'notificationType' => $this->notificationType,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }

    private function defaultSubject(string $notificationType): string
    {
        return match ($notificationType) {
            'created' => 'Calendar Event Notification: ' . $this->event->title,
            'event_time' => 'Calendar Event Time Reminder: ' . $this->event->title,
            'reminder_10min' => 'Calendar Event 10 Minute Reminder: ' . $this->event->title,
            'day_before' => 'Calendar Event 1 Day Before Reminder: ' . $this->event->title,
            'day_of_6am' => 'Calendar Event 6 AM Reminder: ' . $this->event->title,
            'one_hour_before' => 'Calendar Event 1 Hour Before Reminder: ' . $this->event->title,
            default => 'Calendar Event Reminder: ' . $this->event->title,
        };
    }
}
