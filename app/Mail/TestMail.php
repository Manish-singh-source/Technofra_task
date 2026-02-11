<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $settings;
    protected $companyName;
    protected $emailSignature;
    protected $predefinedHeader;
    protected $predefinedFooter;

    /**
     * Create a new message instance.
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->companyName = $settings['company_name'] ?? 'CRM System';
        $this->emailSignature = $settings['email_signature'] ?? '';
        $this->predefinedHeader = $settings['predefined_header'] ?? '';
        $this->predefinedFooter = $settings['predefined_footer'] ?? '';
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Test Email - ' . $this->companyName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.test',
            with: [
                'companyName' => $this->companyName,
                'emailSignature' => $this->emailSignature,
                'predefinedHeader' => $this->predefinedHeader,
                'predefinedFooter' => $this->predefinedFooter,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
}
