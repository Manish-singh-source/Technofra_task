<?php

namespace App\Console\Commands;

use App\Mail\AmcVisitReminderMail;
use App\Models\AmcServiceDetail;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAmcVisitReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amc:send-visit-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send AMC visit reminders for pending visits due tomorrow and today';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for AMC visit reminders...');

        $settings = Setting::getAllSettings();
        $enabledRaw = strtolower(trim((string) ($settings['renewal_notifications_enabled'] ?? '1')));

        if (in_array($enabledRaw, ['0', 'false', 'off', 'no'], true)) {
            $this->info('AMC reminders are disabled because renewal notifications are disabled in settings.');

            return Command::SUCCESS;
        }

        $adminEmail = trim((string) ($settings['renewal_admin_email'] ?? ''));
        if ($adminEmail === '') {
            $adminEmail = trim((string) ($settings['company_email'] ?? ''));
        }
        if ($adminEmail === '') {
            $adminEmail = (string) env('ADMIN_EMAIL', '');
        }

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Valid renewal admin email is not configured.');

            return Command::FAILURE;
        }

        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        $beforeVisitReminders = AmcServiceDetail::query()
            ->with(['amcService.service.client', 'amcService.service.vendor'])
            ->where('status', 'pending')
            ->whereDate('visit_date', $tomorrow)
            ->whereNull('before_visit_reminder_sent_at')
            ->orderBy('visit_date')
            ->orderBy('visit_number')
            ->get();

        $sameDayReminders = AmcServiceDetail::query()
            ->with(['amcService.service.client', 'amcService.service.vendor'])
            ->where('status', 'pending')
            ->whereDate('visit_date', $today)
            ->whereNull('same_day_reminder_sent_at')
            ->orderBy('visit_date')
            ->orderBy('visit_number')
            ->get();

        if ($beforeVisitReminders->isEmpty() && $sameDayReminders->isEmpty()) {
            $this->info('No pending AMC visits are due for reminder today.');

            return Command::SUCCESS;
        }

        try {
            $this->applyMailSettings($settings);

            Mail::to($adminEmail)->send(new AmcVisitReminderMail(
                $beforeVisitReminders,
                $sameDayReminders,
                $today->toDateString(),
                $tomorrow->toDateString()
            ));

            $now = now();

            foreach ($beforeVisitReminders as $detail) {
                $detail->forceFill([
                    'before_visit_reminder_sent_at' => $now,
                ])->save();
            }

            foreach ($sameDayReminders as $detail) {
                $detail->forceFill([
                    'same_day_reminder_sent_at' => $now,
                ])->save();
            }

            $this->info(sprintf(
                'AMC reminder email sent to %s. Tomorrow: %d, Today: %d.',
                $adminEmail,
                $beforeVisitReminders->count(),
                $sameDayReminders->count()
            ));

            // If you want clients to receive the same reminder later, uncomment this block.
            /*
            $clientEmails = collect([$beforeVisitReminders, $sameDayReminders])
                ->flatten(1)
                ->map(fn (AmcServiceDetail $detail) => $detail->amcService?->service?->client?->email)
                ->filter()
                ->unique()
                ->values();

            foreach ($clientEmails as $clientEmail) {
                Mail::to($clientEmail)->send(new AmcVisitReminderMail(
                    $beforeVisitReminders,
                    $sameDayReminders,
                    $today->toDateString(),
                    $tomorrow->toDateString()
                ));
            }
            */

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Failed to send AMC visit reminder email: ' . $exception->getMessage());

            return Command::FAILURE;
        }
    }

    private function applyMailSettings(array $settings): void
    {
        $protocol = (string) ($settings['email_protocol'] ?? config('mail.default', 'smtp'));
        if (! in_array($protocol, ['smtp', 'sendmail', 'mail'], true)) {
            $protocol = 'smtp';
        }

        $encryption = (string) ($settings['email_encryption'] ?? 'tls');
        if ($encryption === 'none' || $encryption === '') {
            $encryption = null;
        }

        config([
            'mail.default' => $protocol,
            'mail.mailer' => $protocol,
            'mail.mailers.smtp.host' => $settings['smtp_host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $settings['smtp_port'] ?? config('mail.mailers.smtp.port', 587),
            'mail.mailers.smtp.username' => $settings['smtp_username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $settings['smtp_password'] ?? config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.from.address' => $settings['email'] ?? config('mail.from.address'),
            'mail.from.name' => $settings['company_name'] ?? config('mail.from.name', 'Technofra Renewal Master'),
        ]);

        app()->forgetInstance('mailer');
        Mail::purge();
    }
}
