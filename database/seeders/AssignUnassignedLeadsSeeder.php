<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\AssignedLead;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignUnassignedLeadsSeeder extends Seeder
{
    public function run(): void
    {
        $staffIds = User::query()
            ->whereIn(DB::raw('LOWER(role)'), ['staff', 'super_admin2'])
            ->pluck('id')
            ->values();

        if ($staffIds->isEmpty()) {
            $this->command?->warn('No staff users found.');
            return;
        }

        $unassignedLeads = Lead::query()
            ->whereDoesntHave('assignedLead')
            ->get();

        if ($unassignedLeads->isEmpty()) {
            $this->command?->info('No unassigned leads found.');
            return;
        }

        DB::transaction(function () use ($unassignedLeads, $staffIds) {
            foreach ($unassignedLeads as $index => $lead) {
                $staffId = (int) $staffIds[$index % $staffIds->count()];

                AssignedLead::updateOrCreate(
                    ['lead_model' => 'lead', 'lead_id' => (int) $lead->id],
                    ['staff_ids' => [$staffId]]
                );

                LeadAssignment::query()->updateOrCreate(
                    ['lead_id' => $lead->id, 'assigned_to' => $staffId],
                    [
                        'assigned_by' => $staffId,
                        'assignment_note' => 'Auto assigned via AssignUnassignedLeadsSeeder',
                        'active' => true,
                        'assigned_at' => now(),
                    ]
                );
            }
        });

        $this->command?->info('Assigned '.$unassignedLeads->count().' unassigned leads to staff users.');
    }
}
