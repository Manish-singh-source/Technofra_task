<?php

namespace Database\Seeders;

use App\Models\StaffLeadStat;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadStatusSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->where('role', 'staff')
            ->pluck('id')
            ->each(function ($staffId) {
                StaffLeadStat::query()->firstOrCreate(
                    ['staff_id' => (int) $staffId],
                    [
                        'total_leads' => 0,
                        'converted_leads' => 0,
                        'lost_leads' => 0,
                        'pending_followups' => 0,
                        'total_calls' => 0,
                        'total_meetings' => 0,
                        'conversion_rate' => 0,
                    ]
                );
            });
    }
}
