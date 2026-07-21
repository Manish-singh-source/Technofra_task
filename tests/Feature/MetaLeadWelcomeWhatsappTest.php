<?php

namespace Tests\Feature;

use App\Jobs\SendMetaLeadWelcomeWhatsappJob;
use App\Models\MetaLead;
use App\Models\MetaLeadWhatsappMessageLog;
use App\Services\MetaLeadService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class MetaLeadWelcomeWhatsappTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.name' => 'Technofra',
            'services.k3_whatsapp.meta_lead_welcome_template' => 'meta_lead_welcome',
            'services.k3_whatsapp.default_language' => 'en',
        ]);
    }

    public function test_new_meta_lead_dispatches_welcome_whatsapp_once(): void
    {
        Queue::fake();

        $lead = $this->storeMetaLead([
            'id' => 'lead-1',
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Asha Sharma']],
                ['name' => 'phone_number', 'values' => ['9876543210']],
            ],
        ]);

        $this->assertSame(MetaLead::WELCOME_WHATSAPP_QUEUED, $lead->fresh()->welcome_whatsapp_status);
        $this->assertDatabaseHas('meta_lead_whatsapp_message_logs', [
            'meta_lead_id' => $lead->id,
            'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
            'template_name' => 'meta_lead_welcome',
        ]);

        Queue::assertPushed(SendMetaLeadWelcomeWhatsappJob::class, 1);
    }

    public function test_existing_meta_lead_update_does_not_dispatch_duplicate_welcome(): void
    {
        Queue::fake();

        MetaLead::create([
            'lead_id' => 'lead-1',
            'full_name' => 'Asha Sharma',
            'phone' => '9876543210',
        ]);

        $this->storeMetaLead([
            'id' => 'lead-1',
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Asha Updated']],
                ['name' => 'phone_number', 'values' => ['9876543210']],
            ],
        ]);

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('meta_lead_whatsapp_message_logs', 0);
    }

    public function test_job_marks_lead_and_log_as_sent_when_whatsapp_succeeds(): void
    {
        $lead = MetaLead::create([
            'lead_id' => 'lead-1',
            'full_name' => 'Asha Sharma',
            'phone' => '9876543210',
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_QUEUED,
        ]);
        $log = MetaLeadWhatsappMessageLog::create([
            'meta_lead_id' => $lead->id,
            'phone' => '9876543210',
            'template_name' => 'meta_lead_welcome',
            'template_parameters' => ['Asha Sharma'],
            'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
        ]);

        $whatsApp = Mockery::mock(WhatsAppService::class);
        $whatsApp->shouldReceive('normalizePhoneNumber')->with('9876543210')->andReturn('919876543210');
        $whatsApp->shouldReceive('sendTemplateMessageWithResult')
            ->once()
            ->with('919876543210', 'meta_lead_welcome', ['Asha Sharma'], 'en', [
                'type' => 'video',
                'link' => 'http://localhost/video/technofra_video.mp4',
            ])
            ->andReturn([
                'successful' => true,
                'phone' => '919876543210',
                'provider_status_code' => 200,
                'provider_response' => ['messages' => [['id' => 'wamid.1']]],
            ]);

        (new SendMetaLeadWelcomeWhatsappJob($lead->id, $log->id))->handle($whatsApp);

        $this->assertSame(MetaLead::WELCOME_WHATSAPP_SENT, $lead->fresh()->welcome_whatsapp_status);
        $this->assertNotNull($lead->fresh()->welcome_whatsapp_sent_at);
        $this->assertSame(MetaLeadWhatsappMessageLog::STATUS_SENT, $log->fresh()->status);
    }

    public function test_job_marks_lead_and_log_as_failed_when_whatsapp_fails(): void
    {
        $lead = MetaLead::create([
            'lead_id' => 'lead-1',
            'full_name' => 'Asha Sharma',
            'phone' => '9876543210',
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_QUEUED,
        ]);
        $log = MetaLeadWhatsappMessageLog::create([
            'meta_lead_id' => $lead->id,
            'phone' => '9876543210',
            'template_name' => 'meta_lead_welcome',
            'template_parameters' => ['Asha Sharma'],
            'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
        ]);

        $whatsApp = Mockery::mock(WhatsAppService::class);
        $whatsApp->shouldReceive('normalizePhoneNumber')->with('9876543210')->andReturn('919876543210');
        $whatsApp->shouldReceive('sendTemplateMessageWithResult')
            ->once()
            ->andReturn([
                'successful' => false,
                'phone' => '919876543210',
                'provider_status_code' => 400,
                'provider_response' => ['error' => 'Bad template'],
                'error_message' => 'Bad template',
            ]);

        (new SendMetaLeadWelcomeWhatsappJob($lead->id, $log->id))->handle($whatsApp);

        $this->assertSame(MetaLead::WELCOME_WHATSAPP_FAILED, $lead->fresh()->welcome_whatsapp_status);
        $this->assertSame(1, $lead->fresh()->welcome_whatsapp_attempts);
        $this->assertSame(MetaLeadWhatsappMessageLog::STATUS_FAILED, $log->fresh()->status);
        $this->assertSame('Bad template', $log->fresh()->error_message);
    }

    public function test_new_meta_lead_without_phone_is_logged_as_skipped(): void
    {
        Queue::fake();

        $lead = $this->storeMetaLead([
            'id' => 'lead-1',
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Asha Sharma']],
            ],
        ]);

        Queue::assertNothingPushed();
        $this->assertSame(MetaLead::WELCOME_WHATSAPP_SKIPPED, $lead->fresh()->welcome_whatsapp_status);
        $this->assertDatabaseHas('meta_lead_whatsapp_message_logs', [
            'meta_lead_id' => $lead->id,
            'status' => MetaLeadWhatsappMessageLog::STATUS_SKIPPED,
        ]);
    }

    public function test_backfill_command_queues_unsent_leads_and_ignores_sent_or_queued(): void
    {
        Queue::fake();

        $pending = MetaLead::create([
            'lead_id' => 'pending-lead',
            'full_name' => 'Pending Lead',
            'phone' => '9876543210',
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_PENDING,
        ]);
        MetaLead::create([
            'lead_id' => 'sent-lead',
            'full_name' => 'Sent Lead',
            'phone' => '9876543211',
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_SENT,
        ]);
        MetaLead::create([
            'lead_id' => 'queued-lead',
            'full_name' => 'Queued Lead',
            'phone' => '9876543212',
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_QUEUED,
        ]);

        $this->artisan('meta:queue-welcome-whatsapp --limit=10')
            ->expectsOutput('Queued 1 Meta lead welcome WhatsApp message(s).')
            ->assertSuccessful();

        Queue::assertPushed(SendMetaLeadWelcomeWhatsappJob::class, 1);
        $this->assertSame(MetaLead::WELCOME_WHATSAPP_QUEUED, $pending->fresh()->welcome_whatsapp_status);
    }

    private function storeMetaLead(array $payload): ?MetaLead
    {
        return (new class extends MetaLeadService {
            public function store(array $payload): ?MetaLead
            {
                return $this->storeLeadData($payload);
            }
        })->store($payload);
    }
}
