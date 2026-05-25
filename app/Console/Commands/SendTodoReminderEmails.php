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
                $occurrenceDate = $todo->getOccurrenceInReminderWindow($now);

                if (!$occurrenceDate) {
                    continue;
                }

                if ($todo->last_reminded_occurrence_on === $occurrenceDate->format('Y-m-d')) {
                    continue;
                }

                $sendEmail = $globalEmailEnabled && (bool) $todo->reminder_email;
                $sendWhatsApp = $globalWhatsAppEnabled && (bool) $todo->reminder_whatsapp;

                if (!$sendEmail && !$sendWhatsApp) {
                    continue;
                }

                $reminderSent = false;
                $phone = $todo->user?->phone;

                if ($sendEmail && $todo->user && !empty($todo->user->email)) {
                    Mail::to($todo->user->email)->send(new TodoReminderMail($todo, $occurrenceDate));
                    $reminderSent = true;
                }

                if ($sendWhatsApp && !empty($phone)) {
                    $message = sprintf(
                        'Todo reminder: %s on %s at %s',
                        (string) $todo->title,
                        $occurrenceDate->format('d M Y'),
                        $todo->getReminderDateTimeForOccurrence($occurrenceDate)->format('h:i A')
                    );

                    if ($whatsAppService->sendMessage($phone, $message)) {
                        $reminderSent = true;
                    }
                }

                if (!$reminderSent) {
                    continue;
                }

                $todo->update([
                    'last_reminded_occurrence_on' => $occurrenceDate->format('Y-m-d'),
                    'last_reminder_sent_at' => $now,
                ]);

                $sentCount++;
            }

            $this->info("Todo reminders sent: {$sentCount}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Todo reminder command failed: ' . $e->getMessage());
            $this->error('Todo reminder command failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
