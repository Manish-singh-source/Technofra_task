<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AmcVisitReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $beforeVisitReminders;
    public $sameDayReminders;
    public $todayDate;
    public $tomorrowDate;

    public function __construct($beforeVisitReminders, $sameDayReminders, string $todayDate, string $tomorrowDate)
    {
        $this->beforeVisitReminders = $beforeVisitReminders;
        $this->sameDayReminders = $sameDayReminders;
        $this->todayDate = $todayDate;
        $this->tomorrowDate = $tomorrowDate;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'AMC Maintenance Visit Reminders',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.amc-visit-reminder',
            with: [
                'beforeVisitReminders' => $this->beforeVisitReminders,
                'sameDayReminders' => $this->sameDayReminders,
                'todayDate' => $this->todayDate,
                'tomorrowDate' => $this->tomorrowDate,
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}
