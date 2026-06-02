<?php

namespace App\Services\CalendarManagement;

use App\Mail\CalendarEventMail;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Notifications\InAppPushNotification;
use App\Models\Setting;
use App\Services\UnifiedNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CalendarNotificationService
{
    public function __construct(
        private readonly UnifiedNotificationService $unifiedNotificationService,
        private readonly WhatsAppService $whatsAppService
    ) {
    }

    public function notifyCreated(CalendarEvent $event): array
    {
        return $this->dispatch($event, 'created');
    }

    public function notifyReminder(CalendarEvent $event, string $reminderType = 'reminder_10min'): array
    {
        return $this->dispatch($event, $reminderType);
    }

    public function notifyEventTime(CalendarEvent $event): array
    {
        return $this->dispatch($event, 'event_time');
    }

    private function dispatch(CalendarEvent $event, string $context): array
    {
        $channels = $this->selectedChannels($event);
        $internalRecipients = $this->internalRecipients($event);

        $title = $this->buildTitle($event, $context);
        $body = $this->buildBody($event, $context);
        $payload = $this->buildPayload($event, $context);

        $summary = [
            'mail' => 0,
            'whatsapp' => 0,
            'app' => 0,
            'web' => 0,
            'channels' => $channels,
        ];

        if (in_array('mail', $channels, true)) {
            $summary['mail'] = $this->sendMail($event, $internalRecipients, $context);
        }

        if (in_array('whatsapp', $channels, true)) {
            $summary['whatsapp'] = $this->sendWhatsApp($event, $internalRecipients, $context);
        }

        $summary['app'] = $this->sendAppNotifications($internalRecipients, $title, $body, $payload, $context);
        $summary['web'] = $this->sendWebNotifications($internalRecipients, $title, $body, $payload, $context);

        return $summary;
    }

    private function sendMail(CalendarEvent $event, Collection $internalRecipients, string $context): int
    {
        if (! $this->isEnabled('auto_calendar_event_email_enabled', true)) {
            return 0;
        }

        $title = $this->buildTitle($event, $context);
        $recipients = collect($event->email_recipients_array)
            ->merge($internalRecipients->pluck('email'))
            ->filter(fn ($email) => filter_var((string) $email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        $sent = 0;
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new CalendarEventMail($event, $context, $title));
                $sent++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to send calendar email notification', [
                    'event_id' => $event->id,
                    'recipient' => $recipient,
                    'context' => $context,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function sendWhatsApp(CalendarEvent $event, Collection $internalRecipients, string $context): int
    {
        if (! $this->isEnabled('auto_calendar_event_whatsapp_enabled', true)) {
            return 0;
        }

        $recipients = collect($event->whatsapp_recipients_array)
            ->merge($internalRecipients->pluck('phone'))
            ->filter()
            ->map(fn ($phone) => trim((string) $phone))
            ->unique()
            ->values()
            ->all();

        if ($recipients === []) {
            return 0;
        }

        try {
            $result = $this->whatsAppService->sendCalendarEventNotification($event, $recipients, $context);

            return (int) ($result['success'] ?? 0);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send calendar WhatsApp notification', [
                'event_id' => $event->id,
                'context' => $context,
                'error' => $exception->getMessage(),
            ]);

            return 0;
        }
    }

    private function sendAppNotifications(
        Collection $internalRecipients,
        string $title,
        string $body,
        array $payload,
        string $context
    ): int {
        if (! $this->isEnabled('mobile_notifications_enabled', true)) {
            return 0;
        }

        $sent = 0;

        foreach ($internalRecipients as $recipient) {
            try {
                $this->unifiedNotificationService->sendPushOnlyToUser($recipient, $title, $body, $payload);
                $sent++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to send calendar app notification', [
                    'user_id' => $recipient->id,
                    'context' => $context,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function sendWebNotifications(
        Collection $internalRecipients,
        string $title,
        string $body,
        array $payload,
        string $context
    ): int {
        $sent = 0;

        foreach ($internalRecipients as $recipient) {
            try {
                $recipient->notify(new InAppPushNotification($title, $body, 'calendar_event', $payload));
                $sent++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to store calendar web notification', [
                    'user_id' => $recipient->id,
                    'context' => $context,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function selectedChannels(CalendarEvent $event): array
    {
        $channels = collect($event->notification_channels_array ?: ['mail', 'whatsapp'])
            ->map(fn ($channel) => strtolower(trim((string) $channel)))
            ->filter()
            ->unique()
            ->values();

        if ($channels->contains('all') || $channels->isEmpty()) {
            return ['mail', 'whatsapp'];
        }

        return $channels->intersect(['mail', 'whatsapp'])->values()->all();
    }

    private function internalRecipients(CalendarEvent $event): Collection
    {
        $recipients = collect();

        foreach ($this->adminUsers() as $admin) {
            $recipients->push($admin);
        }

        $creator = $event->creator;
        if ($creator instanceof User && $creator->isStaff()) {
            $recipients->push($creator);
        }

        return $recipients
            ->filter(fn (User $user) => ! blank($user->id))
            ->unique('id')
            ->values();
    }

    private function adminUsers(): Collection
    {
        return User::query()
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhere('role', 'super_admin')
                    ->orWhere('role', 'super-admin')
                    ->orWhere('role', 'super_admin2');
            })
            ->get();
    }

    private function isEnabled(string $settingKey, bool $default = true): bool
    {
        $raw = strtolower((string) Setting::get($settingKey, $default ? '1' : '0'));

        return ! in_array($raw, ['0', 'false', 'off', 'no'], true);
    }

    private function buildTitle(CalendarEvent $event, string $context): string
    {
        $suffix = match ($context) {
            'created' => 'Created',
            CalendarEvent::REMINDER_WINDOW_DAY_BEFORE => '1 Day Before Reminder',
            CalendarEvent::REMINDER_WINDOW_DAY_OF_6AM => '6 AM Reminder',
            CalendarEvent::REMINDER_WINDOW_ONE_HOUR_BEFORE => '1 Hour Before Reminder',
            'event_time' => 'Event Time Reminder',
            'reminder_10min' => '10 Minute Reminder',
            default => ucfirst(str_replace('_', ' ', $context)),
        };

        return sprintf('Calendar Event %s: %s', $suffix, $event->title);
    }

    private function buildBody(CalendarEvent $event, string $context): string
    {
        $date = optional($event->event_date)?->format('d M Y') ?? 'N/A';
        $time = optional($event->event_time)?->format('h:i A') ?? 'N/A';
        $lead = match ($context) {
            'created' => 'A new calendar event has been created.',
            CalendarEvent::REMINDER_WINDOW_DAY_BEFORE => 'This is your one-day-before reminder for the scheduled calendar event.',
            CalendarEvent::REMINDER_WINDOW_DAY_OF_6AM => 'This is your 6:00 AM reminder for the scheduled calendar event.',
            CalendarEvent::REMINDER_WINDOW_ONE_HOUR_BEFORE => 'This is your one-hour-before reminder for the scheduled calendar event.',
            'event_time' => 'This is the event-time reminder for your scheduled calendar event.',
            'reminder_10min' => 'This is your 10-minute reminder for the scheduled calendar event.',
            default => 'This is a calendar event notification.',
        };

        return trim(sprintf('%s Event: %s. Scheduled for %s at %s.', $lead, $event->title, $date, $time));
    }

    private function buildPayload(CalendarEvent $event, string $context): array
    {
        return [
            'calendar_event_id' => $event->id,
            'title' => $event->title,
            'context' => $context,
            'event_date' => optional($event->event_date)?->toDateString(),
            'event_time' => optional($event->event_time)?->format('H:i'),
            'created_by' => $event->created_by,
            'reminder_window_label' => match ($context) {
                CalendarEvent::REMINDER_WINDOW_DAY_BEFORE => '1 Day Before',
                CalendarEvent::REMINDER_WINDOW_DAY_OF_6AM => '6 AM',
                CalendarEvent::REMINDER_WINDOW_ONE_HOUR_BEFORE => '1 Hour Before',
                default => ucfirst(str_replace('_', ' ', $context)),
            },
        ];
    }
}
