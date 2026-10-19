<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $alertType,
        public readonly string $alertLabel,
        public readonly object $lead,
        public readonly string $leadUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Lead Alert: {$this->alertLabel} - {$this->lead->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lead-alert');
    }
}
