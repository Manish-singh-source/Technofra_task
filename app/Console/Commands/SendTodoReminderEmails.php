<?php

namespace App\Console\Commands;

use App\Mail\TodoReminderMail;
use App\Models\Todo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTodoReminderEmails extends Command
{
    protected $signature = 'todos:send-reminders';

    protected $description = 'Send todo reminder emails for due recurring todos';

    public function handle()
    {
        $now = now();
        $sentCount = 0;

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

                if (!$todo->user || empty($todo->user->email)) {
                    continue;
                }

                Mail::to($todo->user->email)->send(new TodoReminderMail($todo, $occurrenceDate));

                $todo->update([
                    'last_reminded_occurrence_on' => $occurrenceDate->format('Y-m-d'),
                    'last_reminder_sent_at' => $now,
                ]);

                $sentCount++;
            }

            $this->info("Todo reminder emails sent: {$sentCount}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Todo reminder email command failed: ' . $e->getMessage());
            $this->error('Todo reminder email command failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
