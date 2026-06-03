<?php

namespace App\Mail;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
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
    public ?string $fromAddress;
    public ?string $fromName;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\CalendarEvent $event
     * @return void
     */
    public function __construct(
        CalendarEvent $event,
        string $notificationType = 'reminder',
        ?string $subjectLine = null,
        ?string $fromAddress = null,
        ?string $fromName = null
    )
    {
        $this->event = $event;
        $this->notificationType = $notificationType;
        $this->subjectLine = $subjectLine ?: $this->defaultSubject($notificationType);
        $this->fromAddress = $fromAddress ?: $this->resolveFromAddress();
        $this->fromName = $fromName ?: $this->resolveFromName();
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $envelope = new Envelope(subject: $this->subjectLine);

        if ($this->fromAddress !== null && filter_var($this->fromAddress, FILTER_VALIDATE_EMAIL)) {
            $envelope = new Envelope(
                subject: $this->subjectLine,
                from: new Address($this->fromAddress, $this->fromName ?: config('mail.from.name', config('app.name'))),
                replyTo: [new Address($this->fromAddress, $this->fromName ?: config('mail.from.name', config('app.name')))],
            );
        }

        return $envelope;
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

    private function resolveFromAddress(): ?string
    {
        $creatorEmail = trim((string) ($this->event->creator->email ?? ''));

        if ($creatorEmail !== '' && filter_var($creatorEmail, FILTER_VALIDATE_EMAIL)) {
            return $creatorEmail;
        }

        $configFromAddress = trim((string) config('mail.from.address', ''));

        return $configFromAddress !== '' ? $configFromAddress : null;
    }

    private function resolveFromName(): string
    {
        $creatorName = trim((string) ($this->event->creator->name ?? ''));

        if ($creatorName !== '') {
            return $creatorName;
        }

        return (string) config('mail.from.name', config('app.name'));
    }
}
