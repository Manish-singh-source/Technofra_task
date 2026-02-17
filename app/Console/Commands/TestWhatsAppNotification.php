<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestWhatsAppNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone} {--template=} {--param=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test K3 WhatsApp template notification by sending to a phone number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $phone = $this->argument('phone');
            $template = $this->option('template') ?: env('K3_WHATSAPP_REMINDER_TEMPLATE', 'calendar_appointment_reminder');
            $params = (array) $this->option('param');

            $this->info('Testing K3 WhatsApp notification...');
            $this->info("Phone: {$phone}");

            if (empty(env('K3_WHATSAPP_BUSINESS_ID')) || empty(env('K3_WHATSAPP_API_KEY'))) {
                $this->error('K3 credentials not configured in .env file.');
                $this->warn('Please add K3_WHATSAPP_BUSINESS_ID and K3_WHATSAPP_API_KEY.');
                return 1;
            }

            $this->info('K3 credentials found in .env');

            if (empty($params)) {
                $params = $this->getDefaultTemplateParams();
            }

            $this->info('Sending template payload:');
            $this->line("Template: {$template}");
            $this->line('Parameters: ' . json_encode($params));
            $this->newLine();

            $whatsappService = new WhatsAppService();
            $result = $whatsappService->sendTemplateMessage($phone, $template, $params);

            if ($result) {
                $this->info('WhatsApp template sent successfully.');
                $this->info("Check your WhatsApp on: {$phone}");
                return 0;
            }

            $this->error('Failed to send WhatsApp template.');
            $this->warn('Check logs for error details:');
            $this->line('storage/logs/laravel.log');
            return 1;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Test WhatsApp notification error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get fallback template params for quick testing.
     *
     * @return array<int, string>
     */
    protected function getDefaultTemplateParams()
    {
        return [
            'Calendar Appointment',
            now()->format('d M Y'),
            now()->addMinutes(10)->format('h:i A'),
        ];
    }
}
