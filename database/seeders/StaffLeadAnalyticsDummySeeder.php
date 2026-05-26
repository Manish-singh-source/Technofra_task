<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignment;
use App\Models\LeadConversion;
use App\Models\LeadFollowup;
use App\Models\LeadStatusHistory;
use App\Models\AssignedLead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffLeadAnalyticsDummySeeder extends Seeder
{
    public function run(): void
    {
        $staffUsers = User::query()
            ->whereIn(DB::raw('LOWER(role)'), ['staff', 'super_admin2'])
            ->orderBy('id')
            ->take(5)
            ->get();

        if ($staffUsers->isEmpty()) {
            $this->command?->warn('No staff users found. Dummy analytics data not seeded.');
            return;
        }

        $statuses = ['new', 'attempted_contact', 'contacted', 'qualified', 'demo_scheduled', 'proposal_sent', 'negotiation', 'won', 'lost'];
        $followupTypes = ['call', 'whatsapp', 'email', 'meeting', 'demo', 'video_call'];
        $outcomes = ['interested', 'callback_later', 'no_response', 'converted', 'negotiation', 'lost'];

        DB::transaction(function () use ($staffUsers, $statuses, $followupTypes, $outcomes) {
            foreach ($staffUsers as $staff) {
                foreach (range(11, 0) as $monthOffset) {
                    $monthStart = now()->copy()->startOfMonth()->subMonths($monthOffset);

                    foreach (range(1, 4) as $i) {
                        $createdAt = $monthStart->copy()->addDays(random_int(0, min(27, $monthStart->daysInMonth - 1)))->addHours(random_int(9, 18));
                        $status = $statuses[array_rand($statuses)];

                        $lead = Lead::query()->create([
                            'name' => 'Analytics Lead '.$staff->id.'-'.$monthStart->format('Ym').'-'.$i,
                            'email' => 'analytics.staff'.$staff->id.'.'.$monthStart->format('Ym').'.'.$i.'@example.com',
                            'phone' => '98'.str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                            'company' => 'Demo Company '.chr(64 + $i),
                            'source' => ['Google', 'Meta', 'Web App', 'Digital Marketing'][array_rand([0,1,2,3])],
                            'priority' => ['low', 'medium', 'high'][array_rand([0,1,2])],
                            'status' => $status,
                            'previous_status' => $status === 'new' ? null : 'contacted',
                            'status_updated_at' => $createdAt->copy()->addDays(random_int(1, 10)),
                            'status_updated_by' => $staff->id,
                            'next_followup_at' => $status === 'won' || $status === 'lost' ? null : $createdAt->copy()->addDays(random_int(-3, 7)),
                            'converted_at' => $status === 'won' ? $createdAt->copy()->addDays(random_int(3, 20)) : null,
                            'lost_at' => $status === 'lost' ? $createdAt->copy()->addDays(random_int(3, 20)) : null,
                            'lost_reason' => $status === 'lost' ? 'Budget constraints' : null,
                            'won_value' => $status === 'won' ? random_int(20000, 250000) : null,
                            'pipeline_stage_order' => array_search($status, $statuses, true) + 1,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt->copy()->addDays(random_int(1, 15)),
                        ]);

                        AssignedLead::updateOrCreate(
                            ['lead_model' => 'lead', 'lead_id' => (int) $lead->id],
                            ['staff_ids' => [(int) $staff->id]]
                        );

                        LeadAssignment::query()->create([
                            'lead_id' => $lead->id,
                            'assigned_to' => $staff->id,
                            'assigned_by' => $staff->id,
                            'assignment_note' => 'Auto seeded for analytics',
                            'active' => true,
                            'assigned_at' => $createdAt,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                        $historyDate = $createdAt->copy()->addDays(1);
                        LeadStatusHistory::query()->create([
                            'lead_id' => $lead->id,
                            'old_status' => 'new',
                            'new_status' => $status,
                            'changed_by' => $staff->id,
                            'remarks' => 'Seeded transition',
                            'changed_at' => $historyDate,
                            'created_at' => $historyDate,
                            'updated_at' => $historyDate,
                        ]);

                        foreach (range(1, random_int(1, 4)) as $fIndex) {
                            $followupAt = $createdAt->copy()->addDays($fIndex)->addHours(random_int(1, 5));
                            $followupType = $followupTypes[array_rand($followupTypes)];
                            $outcome = $outcomes[array_rand($outcomes)];

                            LeadFollowup::query()->create([
                                'lead_id' => $lead->id,
                                'staff_id' => $staff->id,
                                'followup_date' => $followupAt,
                                'followup_type' => $followupType,
                                'outcome' => $outcome,
                                'discussion_notes' => 'Followup seeded for analytics charts.',
                                'next_followup_date' => $followupAt->copy()->addDays(random_int(1, 5)),
                                'lead_status_after_followup' => $status,
                                'reminder_sent' => (bool) random_int(0, 1),
                                'created_at' => $followupAt,
                                'updated_at' => $followupAt,
                            ]);

                            LeadActivity::query()->create([
                                'lead_id' => $lead->id,
                                'user_id' => $staff->id,
                                'activity_type' => ['followup_added', 'status_changed', 'lead_assigned', 'reminder_sent'][array_rand([0,1,2,3])],
                                'description' => 'Seeded activity for staff analytics.',
                                'metadata' => ['source' => 'dummy_seeder', 'outcome' => $outcome],
                                'created_at' => $followupAt,
                                'updated_at' => $followupAt,
                            ]);
                        }

                        if ($status === 'won') {
                            LeadConversion::query()->firstOrCreate(
                                ['lead_id' => $lead->id],
                                [
                                    'client_id' => null,
                                    'converted_by' => $staff->id,
                                    'conversion_value' => $lead->won_value,
                                    'converted_at' => $lead->converted_at ?: $createdAt->copy()->addDays(7),
                                    'created_at' => $lead->converted_at ?: $createdAt->copy()->addDays(7),
                                    'updated_at' => $lead->converted_at ?: $createdAt->copy()->addDays(7),
                                ]
                            );
                        }
                    }
                }
            }
        });

        $this->command?->info('Staff lead analytics dummy data seeded successfully.');
    }
}
