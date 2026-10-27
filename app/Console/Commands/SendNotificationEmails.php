<?php

namespace App\Console\Commands;

use App\Mail\NotificationMail;
use App\Models\Service;
use App\Models\Setting;
use App\Models\VendorService;
use App\Models\User;
use App\Helpers\FcmNotificationHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class SendNotificationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily renewal notification email to admin for upcoming renewals';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Sending daily renewal notification email...');

        $settings = Setting::getAllSettings();
        $enabledRaw = strtolower(trim((string) ($settings['renewal_notifications_enabled'] ?? '1')));
        if (in_array($enabledRaw, ['0', 'false', 'off', 'no'], true)) {
            $this->info('Renewal notifications are disabled in settings.');
            return Command::SUCCESS;
        }

        $adminEmail = trim((string) ($settings['renewal_admin_email'] ?? ''));
        if ($adminEmail === '') {
            $adminEmail = trim((string) ($settings['company_email'] ?? ''));
        }
        if ($adminEmail === '') {
            $adminEmail = (string) env('ADMIN_EMAIL', '');
        }

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Valid renewal admin email is not configured.');
            return Command::FAILURE;
        }

        $noticeDays = (int) ($settings['renewal_notice_days'] ?? 5);
        if ($noticeDays < 1) {
            $noticeDays = 1;
        }
        if ($noticeDays > 30) {
            $noticeDays = 30;
        }

        $today = Carbon::today();
        $windowEnd = $today->copy()->addDays($noticeDays);

        $upcomingServices = Service::with(['client', 'vendor'])
            ->whereBetween('end_date', [$today, $windowEnd])
            ->orderBy('end_date')
            ->get();

        $upcomingVendorServices = VendorService::with('vendor')
            ->whereBetween('end_date', [$today, $windowEnd])
            ->orderBy('end_date')
            ->get();

        if ($upcomingServices->isEmpty() && $upcomingVendorServices->isEmpty()) {
            $this->info("No services are due in the next {$noticeDays} day(s). No email sent.");
            return Command::SUCCESS;
        }

        $this->sendRenewalPushes($upcomingServices, $upcomingVendorServices, $today);

        try {
            $this->applyMailSettings($settings);
            $defaultTheme = strtolower(trim((string) ($settings['default_theme'] ?? $settings['theme'] ?? 'white')));
            Mail::to($adminEmail)->send(new NotificationMail($upcomingServices, $upcomingVendorServices, $defaultTheme));

            $this->info("Renewal email sent to {$adminEmail}.");
            $this->info("Client services: {$upcomingServices->count()}, Vendor services: {$upcomingVendorServices->count()}.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send renewal notification email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function sendRenewalPushes($services, $vendorServices, Carbon $today): void
    {
        $recipients = User::query()->whereIn('role', ['admin', 'super_admin', 'super-admin', 'super_admin2'])->get();

        foreach ($recipients as $recipient) {
            foreach ($services as $service) {
                $key = 'renewal-fcm:client:'.$service->id.':'.$recipient->id.':'.$today->toDateString();
                if (Cache::add($key, true, now()->endOfDay())) {
                    FcmNotificationHelper::sendToUser($recipient, 'Client renewal reminder', ($service->service_name ?: 'Client service').' expires on '.optional($service->end_date)->format('d M Y').'.', ['service_id' => (string) $service->id, 'type' => 'renewal']);
                }
            }

            foreach ($vendorServices as $service) {
                $key = 'renewal-fcm:vendor:'.$service->id.':'.$recipient->id.':'.$today->toDateString();
                if (Cache::add($key, true, now()->endOfDay())) {
                    FcmNotificationHelper::sendToUser($recipient, 'Vendor renewal reminder', ($service->service_name ?: 'Vendor service').' expires on '.optional($service->end_date)->format('d M Y').'.', ['vendor_service_id' => (string) $service->id, 'type' => 'renewal']);
                }
            }
        }
    }

    private function applyMailSettings(array $settings): void
    {
        $protocol = (string) ($settings['email_protocol'] ?? config('mail.default', 'smtp'));
        if (!in_array($protocol, ['smtp', 'sendmail', 'mail'], true)) {
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

