<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $criticalServices;
    public $criticalVendorServices;
    public $defaultTheme;

    /**
     * Create a new message instance.
     *
     * @param mixed $criticalServices
     * @param mixed $criticalVendorServices
     * @param string $defaultTheme
     */
    public function __construct($criticalServices, $criticalVendorServices, string $defaultTheme = 'white')
    {
        $this->criticalServices = $criticalServices;
        $this->criticalVendorServices = $criticalVendorServices;
        $this->defaultTheme = $defaultTheme;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Service Renewal Notifications - Action Required',
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
            view: 'emails.notifications',
            with: [
                'criticalServices' => $this->criticalServices,
                'criticalVendorServices' => $this->criticalVendorServices,
                'defaultTheme' => $this->defaultTheme,
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
}
