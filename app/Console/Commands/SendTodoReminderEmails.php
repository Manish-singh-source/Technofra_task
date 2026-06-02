<?php

namespace App\Console\Commands;

use App\Mail\TodoReminderMail;
use App\Models\Setting;
use App\Models\Todo;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTodoReminderEmails extends Command
{
    protected $signature = 'todos:send-reminders';

    protected $description = 'Send todo reminders (email/WhatsApp) for due recurring todos';

    public function handle()
    {
        $now = now();
        $sentCount = 0;
        $windowCounts = [
            Todo::REMINDER_WINDOW_DAY_BEFORE => 0,
            Todo::REMINDER_WINDOW_DAY_OF_6AM => 0,
            Todo::REMINDER_WINDOW_ONE_HOUR_BEFORE => 0,
        ];
        $whatsAppService = new WhatsAppService();
        $globalEmailEnabled = !in_array(
            strtolower((string) Setting::get('auto_todo_reminder_email_enabled', '1')),
            ['0', 'false', 'off', 'no'],
            true
        );
        $globalWhatsAppEnabled = !in_array(
            strtolower((string) Setting::get('auto_todo_reminder_whatsapp_enabled', '1')),
            ['0', 'false', 'off', 'no'],
            true
        );

        try {
            $todos = Todo::with('user')->incomplete()->get();

            foreach ($todos as $todo) {
                $dueReminderWindows = $todo->getDueReminderWindows($now);

                foreach ($dueReminderWindows as $dueReminder) {
                    $occurrenceDate = $dueReminder['occurrence_date'];
                    $window = $dueReminder['window'];

                    $sendEmail = $globalEmailEnabled && (bool) $todo->reminder_email;
                    $sendWhatsApp = $globalWhatsAppEnabled && (bool) $todo->reminder_whatsapp;

                    if (! $sendEmail && ! $sendWhatsApp) {
                        continue;
                    }

                    $reminderSent = false;
                    $phone = $todo->user?->phone;
                    $windowLabel = $todo->getReminderWindowLabel($window);
                    $reminderTime = $todo->getReminderDateTimeForOccurrence($occurrenceDate)->format('h:i A');

                    if ($sendEmail && $todo->user && !empty($todo->user->email)) {
                        Mail::to($todo->user->email)->send(new TodoReminderMail($todo, $occurrenceDate, $window));
                        $reminderSent = true;
                    }

                    if ($sendWhatsApp && !empty($phone)) {
                        $message = sprintf(
                            'Todo reminder (%s): %s on %s at %s',
                            $windowLabel,
                            (string) $todo->title,
                            $occurrenceDate->format('d M Y'),
                            $reminderTime
                        );

                        if ($whatsAppService->sendMessage($phone, $message)) {
                            $reminderSent = true;
                        }
                    }

                    if (! $reminderSent) {
                        continue;
                    }

                    $todo->markReminderWindowAsSentForOccurrence($window, $occurrenceDate, $now);
                    $todo->last_reminded_occurrence_on = $occurrenceDate->format('Y-m-d');
                    $todo->last_reminder_sent_at = $now;
                    $todo->save();

                    $windowCounts[$window] = ($windowCounts[$window] ?? 0) + 1;
                    $sentCount++;
                }
            }

            $this->info("Todo reminders sent: {$sentCount}");
            foreach ($windowCounts as $window => $count) {
                $this->info(sprintf('%s reminders sent: %d', str_replace('_', ' ', $window), $count));
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Todo reminder command failed: ' . $e->getMessage());
            $this->error('Todo reminder command failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
