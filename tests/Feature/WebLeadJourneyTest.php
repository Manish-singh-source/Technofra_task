<?php

namespace Tests\Feature;

use App\Http\View\Composers\NotificationComposer;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Uses an explicit, disposable in-memory database; never runs project migrations or RefreshDatabase. */
class WebLeadJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Carbon::setTestNow(Carbon::parse('2026-10-17 12:00:00'));
        Queue::fake();
        $this->withoutExceptionHandling([\Illuminate\Validation\ValidationException::class]);
        (require database_path('migrations/2026_01_29_113447_create_permission_tables.php'))->up();
        $this->app->instance(NotificationComposer::class, new class extends NotificationComposer {
            public function compose(\Illuminate\View\View $view) {}
        });
        $this->app['view']->getFinder()->prependLocation(__DIR__ . '/../Fixtures/lead-views');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['first_name', 'last_name', 'email', 'phone', 'password', 'role', 'status'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'email', 'phone', 'company', 'company_name', 'source', 'status', 'previous_status', 'lost_reason', 'tags', 'description', 'requirements', 'website'] as $column) {
                $table->text($column)->nullable();
            }
            foreach (['next_followup_at', 'converted_at', 'lost_at', 'status_updated_at'] as $column) {
                $table->dateTime($column)->nullable();
            }
            foreach (['created_by', 'status_updated_by', 'pipeline_stage_order'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->decimal('won_value')->nullable();
            $table->decimal('lead_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('assigned_leads', function (Blueprint $table) {
            $table->id(); $table->string('lead_model'); $table->integer('lead_id');
            $table->json('staff_ids'); $table->timestamps();
        });
        foreach (['digital_marketing_leads', 'webapp_leads', 'meta_leads', 'google_leads', 'contactform', 'enquiry_forms'] as $source) {
            Schema::create($source, function (Blueprint $table) {
                $table->id(); $table->string('status')->default('new');
                foreach (['name', 'full_name', 'email', 'phone', 'company', 'contact'] as $column) {
                    $table->string($column)->nullable();
                }
                $table->timestamps(); $table->softDeletes();
            });
        }
        Schema::create('meta_lead_user', function (Blueprint $table) {
            $table->integer('user_id'); $table->integer('meta_lead_id');
        });
        (require database_path('migrations/2026_05_26_000200_create_enterprise_lead_pipeline_tables.php'))->up();
        (require database_path('migrations/2026_05_26_000400_add_changed_at_and_pipeline_stages.php'))->up();
        (require database_path('migrations/2026_05_26_020100_add_source_fields_to_lead_followups_table.php'))->up();
        $admin = User::create(['first_name' => 'Sales', 'last_name' => 'Admin', 'email' => 'admin@example.test', 'role' => 'admin']);
        $admin->setRelation('roles', new Collection());
        $this->actingAs($admin);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function lead(array $attributes = []): Lead
    {
        return Lead::create(array_merge(['name' => 'Asha Sharma', 'email' => 'asha@example.test', 'status' => 'new', 'source' => 'website', 'created_by' => auth()->id()], $attributes));
    }

    private function conversation(array $attributes = []): array
    {
        return array_merge(['followup_date' => '2026-10-17T11:30', 'followup_type' => 'call', 'outcome' => 'interested', 'discussion_notes' => 'Discussed website requirements.', 'complete_scheduled_followup' => '1'], $attributes);
    }

    public function test_worklist_and_profile_render_with_next_action_and_conversation_history(): void
    {
        $lead = $this->lead(['next_followup_at' => now()->subHour()]);
        $lead->forceFill(['created_at' => now()->subDays(2)])->save();
        LeadFollowup::create(['lead_id' => $lead->id, 'staff_id' => auth()->id(), 'followup_date' => now()->subHours(2), 'followup_type' => 'call', 'outcome' => 'interested', 'discussion_notes' => 'Requested a proposal.']);
        $list = $this->get('/lead-management?work=overdue')->assertOk()->assertSee('Asha Sharma')->assertSee('Requested a proposal.')->assertSee('Next follow-up');
        $profile = $this->get('/lead-management/lead/' . $lead->id . '/view')->assertOk()->assertSee('What happened to this lead?')->assertSee('Follow-up overdue')->assertSee('Requested a proposal.');
        if (getenv('LEAD_JOURNEY_PREVIEW') === '1') {
            file_put_contents(storage_path('app/lead-worklist-preview.html'), $list->getContent());
            file_put_contents(storage_path('app/lead-profile-preview.html'), $profile->getContent());
        }
    }

    public function test_empty_filtered_worklist_still_shows_filter_controls(): void
    {
        $this->get('/lead-management?work=overdue')->assertOk()->assertSee('All statuses')->assertSee('No leads match these filters.');
    }

    public function test_logging_a_conversation_updates_next_date_and_records_status_history(): void
    {
        $lead = $this->lead(['next_followup_at' => now()->subHour()]);
        $this->post('/lead-management/lead/' . $lead->id . '/followup', $this->conversation([
            'lead_status_after_followup' => 'qualified', 'next_followup_date' => '2026-10-19T10:00',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('qualified', $lead->fresh()->status);
        $this->assertSame('2026-10-19 10:00', $lead->fresh()->next_followup_at->format('Y-m-d H:i'));
        $this->assertDatabaseHas('lead_status_histories', ['lead_id' => $lead->id, 'new_status' => 'qualified']);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'activity_type' => 'followup_added']);
    }

    public function test_completed_followup_without_a_new_date_clears_pending_work(): void
    {
        $lead = $this->lead(['next_followup_at' => now()->subHour()]);
        $this->post('/lead-management/lead/' . $lead->id . '/followup', $this->conversation())->assertSessionHasNoErrors();
        $this->assertNull($lead->fresh()->next_followup_at);
        $this->assertSame(1, LeadFollowup::count());
    }

    public function test_callback_requires_a_future_next_date_and_lost_requires_a_reason(): void
    {
        $lead = $this->lead();
        $url = '/lead-management/lead/' . $lead->id . '/followup';
        $this->post($url, $this->conversation(['outcome' => 'callback_later']))->assertSessionHasErrors('next_followup_date');
        $this->post($url, $this->conversation(['lead_status_after_followup' => 'lost', 'outcome' => 'lost']))->assertSessionHasErrors('lost_reason');
        $this->assertSame(0, LeadFollowup::count());
    }

    public function test_followup_cannot_bypass_backwards_transition_or_reopen_closed_lead(): void
    {
        $lead = $this->lead(['status' => 'qualified']);
        $this->post('/lead-management/lead/' . $lead->id . '/followup', $this->conversation(['lead_status_after_followup' => 'new']))->assertSessionHasErrors('status');
        $lead->update(['status' => 'lost']);
        $this->post('/lead-management/lead/' . $lead->id . '/followup', $this->conversation())->assertSessionHasErrors('followup_date');
        $this->assertSame(0, LeadFollowup::count());
    }

    public function test_schedule_changes_the_plan_without_creating_a_fake_conversation(): void
    {
        $lead = $this->lead();
        $this->post('/lead-management/lead/' . $lead->id . '/next-followup', ['next_followup_at' => '2026-10-18T14:00', 'reason' => 'Call to review the proposal.'])->assertSessionHasNoErrors();
        $this->assertSame('2026-10-18 14:00', $lead->fresh()->next_followup_at->format('Y-m-d H:i'));
        $this->assertSame(0, LeadFollowup::count());
        $this->assertDatabaseHas('lead_activities', ['activity_type' => 'followup_scheduled', 'lead_id' => $lead->id]);
    }

    public function test_lost_status_clears_next_action_and_preserves_reason(): void
    {
        $lead = $this->lead(['next_followup_at' => now()->addDay()]);
        $this->patch('/lead-management/lead/' . $lead->id . '/status', ['status' => 'lost', 'lost_reason' => 'Budget not approved.'])->assertSessionHasNoErrors();
        $this->assertNull($lead->fresh()->next_followup_at);
        $this->assertSame('Budget not approved.', $lead->fresh()->lost_reason);
    }

    public function test_conversion_rejects_an_existing_team_email_without_mutating_user(): void
    {
        $lead = $this->lead(['email' => 'admin@example.test']);
        $this->patch('/lead-management/lead/' . $lead->id . '/status', ['status' => 'converted'])->assertSessionHasErrors('status');
        $this->assertSame('admin', auth()->user()->fresh()->getRawOriginal('role'));
        $this->assertSame('new', $lead->fresh()->status);
    }

    public function test_blank_external_contacts_do_not_attach_to_the_first_unrelated_lead(): void
    {
        $unrelated = $this->lead();
        $id = DB::table('digital_marketing_leads')->insertGetId(['name' => 'No contact lead', 'status' => 'new']);
        $url = '/lead-management/digital_marketing/' . $id . '/view';
        $this->get($url)->assertOk()->assertSee('No contact lead');
        $this->get($url)->assertOk();
        $this->assertSame(2, Lead::count());
        $this->assertSame('Asha Sharma', $unrelated->fresh()->name);
    }

    public function test_external_followup_syncs_source_status_and_preserves_journey_remarks(): void
    {
        $id = DB::table('digital_marketing_leads')->insertGetId(['name' => 'External lead', 'email' => 'external@example.test', 'status' => 'new']);
        $this->post('/lead-management/digital_marketing/' . $id . '/followup', $this->conversation([
            'lead_status_after_followup' => 'qualified', 'next_followup_date' => '2026-10-19T10:00',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('digital_marketing_leads', ['id' => $id, 'status' => 'qualified']);
        $this->get('/lead-management/digital_marketing/' . $id . '/view')->assertOk()->assertSee('Stage changed from New to Qualified')->assertSee('Discussed website requirements.');
        $this->assertSame(1, Lead::count());
    }

    public function test_closed_conversion_via_followup_creates_customer_once_and_clears_pending_date(): void
    {
        $lead = $this->lead(['next_followup_at' => now()->addDay()]);
        $this->post('/lead-management/lead/' . $lead->id . '/followup', $this->conversation([
            'outcome' => 'converted', 'lead_status_after_followup' => 'converted', 'won_value' => '5000',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'asha@example.test', 'role' => 'client']);
        $this->assertDatabaseHas('lead_conversions', ['lead_id' => $lead->id]);
        $this->assertNull($lead->fresh()->next_followup_at);
        $this->get('/lead-management/lead/' . $lead->id . '/view')->assertOk()->assertSee('Lead closed')->assertDontSee('id="logFollowupModal"', false);
        $this->post('/lead-management/lead/' . $lead->id . '/followup', $this->conversation())->assertSessionHasErrors();
        $this->assertSame(1, DB::table('lead_conversions')->count());
    }

    public function test_future_auto_scheduled_activity_is_not_shown_as_a_completed_conversation(): void
    {
        $lead = $this->lead();
        LeadFollowup::create(['lead_id' => $lead->id, 'followup_date' => now()->addDay(), 'followup_type' => 'demo', 'discussion_notes' => 'Future auto-generated meeting']);
        $this->get('/lead-management/lead/' . $lead->id . '/view')->assertOk()->assertSee('No conversation logged')->assertDontSee('Future auto-generated meeting');
    }
}
