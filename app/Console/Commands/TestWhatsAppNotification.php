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
    protected $signature = 'whatsapp:test {phone} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp notification by sending a test message to a phone number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $phone = $this->argument('phone');
            $customMessage = $this->option('message');
            
            $this->info("Testing WhatsApp notification...");
            $this->info("Phone: {$phone}");
            
            // Check Twilio configuration
            if (empty(env('TWILIO_ACCOUNT_SID')) || empty(env('TWILIO_AUTH_TOKEN'))) {
                $this->error("❌ Twilio credentials not configured in .env file!");
                $this->warn("Please add TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN to your .env file.");
                return 1;
            }
            
            $this->info("✓ Twilio credentials found in .env");
            
            // Create WhatsApp service
            $whatsappService = new WhatsAppService();
            
            // Prepare test message
            $message = $customMessage ?: $this->getTestMessage();
            
            $this->info("\nSending message:");
            $this->line($message);
            $this->newLine();
            
            // Send message
            $result = $whatsappService->sendMessage($phone, $message);
            
            if ($result) {
                $this->info("✅ WhatsApp message sent successfully!");
                $this->info("Check your WhatsApp on: {$phone}");
                $this->newLine();
                $this->warn("Note: If using Twilio Sandbox, make sure the phone number has joined the sandbox.");
                $this->warn("Send 'join <your-code>' to +1 415 523 8886 from WhatsApp to join.");
                return 0;
            } else {
                $this->error("❌ Failed to send WhatsApp message!");
                $this->warn("Check the logs for more details:");
                $this->line("storage/logs/laravel.log");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Test WhatsApp notification error: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Get default test message
     *
     * @return string
     */
    protected function getTestMessage()
    {
        return "🧪 *Test Message from Technofra*\n\n" .
               "This is a test WhatsApp notification.\n\n" .
               "If you received this message, your WhatsApp integration is working correctly! ✅\n\n" .
               "---\n" .
               "_Technofra Renewal Master_";
    }
}

