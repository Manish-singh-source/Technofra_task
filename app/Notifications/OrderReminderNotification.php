<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $order, public string $type, public ?int $days = null) {}

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        $timing = $this->days === null
            ? 'is still waiting for vendor receipt'
            : ($this->days < 0 ? 'is overdue by '.abs($this->days).' day(s)' : ($this->days === 0 ? 'is due today' : 'is due in '.$this->days.' day(s)'));

        return ['order_id' => $this->order->id, 'type' => 'order_reminder', 'message' => $this->type.' for '.$this->order->order_no.' '.$timing.'.', 'url' => route('orders.show', $this->order)];
    }

    public function toFcm($notifiable): array
    {
        return ['title' => $this->type, 'body' => 'Vendor receipt is still pending for '.$this->order->order_no.'.', 'data' => ['order_id' => (string) $this->order->id, 'type' => 'order_reminder']];
    }
}
