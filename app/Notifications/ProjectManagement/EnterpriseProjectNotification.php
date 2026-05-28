<?php

namespace App\Notifications\ProjectManagement;

use App\Notifications\Channels\WhatsAppReadyChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnterpriseProjectNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly string $type,
        private readonly array $data = [],
        private readonly bool $sendEmail = true,
        private readonly bool $sendWhatsAppReady = true
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        if ($this->sendWhatsAppReady) {
            $channels[] = WhatsAppReadyChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello!')
            ->line($this->body)
            ->line('Open MyCRM for details.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,
        ];
    }

    public function toWhatsAppReady(object $notifiable): array
    {
        return [
            'to' => $notifiable->phone ?? null,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
