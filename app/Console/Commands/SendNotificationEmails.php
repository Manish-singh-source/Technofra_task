<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\VendorService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

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
    protected $description = 'Send daily WhatsApp notifications for critical services';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Sending daily WhatsApp notifications...');

        $today = Carbon::today();
        $fiveDaysFromNow = $today->copy()->addDays(5);

        // Get critical client services (overdue + expiring within next 5 days)
        $criticalServices = Service::with(['client', 'vendor'])
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('end_date', '<', $today) // Overdue
                      ->orWhereBetween('end_date', [$today, $fiveDaysFromNow]); // Upcoming
            })
            ->orderByRaw('CASE WHEN end_date < ? THEN 0 ELSE 1 END, end_date ASC', [$today])
            ->get();

        // Get critical vendor services (overdue + expiring within next 5 days)
        $criticalVendorServices = VendorService::with('vendor')
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('end_date', '<', $today) // Overdue
                      ->orWhereBetween('end_date', [$today, $fiveDaysFromNow]); // Upcoming
            })
            ->orderByRaw('CASE WHEN end_date < ? THEN 0 ELSE 1 END, end_date ASC', [$today])
            ->get();

        if ($criticalServices->isEmpty() && $criticalVendorServices->isEmpty()) {
            $this->info('No critical services or vendor services found. No WhatsApp reminders sent.');
            return 0;
        }

        $adminPhone = (string) config('services.k3_whatsapp.admin_phone');
        $renewalTemplate = (string) config('services.k3_whatsapp.renewal_template', 'renewal_reminder_upcoming');
        $hasFiveDayFlag = Schema::hasColumn('services', 'five_days_notified');
        $adminReminderServicesQuery = Service::with('client')
            ->whereDate('end_date', $fiveDaysFromNow);

        if ($hasFiveDayFlag) {
            $adminReminderServicesQuery->where(function ($query) {
                $query->whereNull('five_days_notified')
                    ->orWhere('five_days_notified', false);
            });
        }

        $adminReminderServices = $adminReminderServicesQuery->get();

        $whatsAppSent = 0;
        $whatsAppFailed = 0;
        $whatsAppSkipped = 0;

        if (empty($adminPhone)) {
            $this->warn('K3_WHATSAPP_ADMIN_PHONE is not configured. Skipping admin WhatsApp reminders.');
            $whatsAppSkipped = $adminReminderServices->count();
        } else {
            $whatsAppService = new WhatsAppService();

            foreach ($adminReminderServices as $service) {
                $params = [
                    (string) (optional($service->client)->cname ?: 'Client'),
                    (string) $service->service_name,
                    $service->end_date->format('d M Y'),
                ];

                if ($whatsAppService->sendTemplateMessage($adminPhone, $renewalTemplate, $params)) {
                    if ($hasFiveDayFlag) {
                        $service->five_days_notified = true;
                        $service->save();
                    }
                    $whatsAppSent++;
                } else {
                    $whatsAppFailed++;
                }
            }
        }

        $this->info('Daily notification run completed.');
        $this->info("Admin WhatsApp (5-day) summary: sent {$whatsAppSent}, failed {$whatsAppFailed}, skipped {$whatsAppSkipped}.");
        return 0;
    }
}
