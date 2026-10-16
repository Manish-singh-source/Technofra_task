<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    private const PREFIX = 'demo_seed_20261012';
    private const PASSWORD = 'Demo@12345';

    public function run(): void
    {
        $faker = FakerFactory::create('en_IN');
        $staff = $this->seedUsers('staff', 10);
        $clients = $this->seedUsers('client', 10);
        $users = array_merge($staff, $clients);
        $operator = User::query()->whereNotIn('role', ['staff', 'client'])->first() ?? $staff[0];

        $this->seedUserDetails($staff, $clients, $faker);
        $vendors = $this->seedVendors($faker);
        $this->seedVendorServices($vendors, $faker);
        $this->seedServices($vendors, $clients, $faker);
        $this->seedAmcServices();
        $projects = $this->seedProjects($clients, $staff, $faker);
        $this->seedTasks($projects, $staff, $faker);
        $this->seedLeads($staff, $faker);
        $this->seedLeadAssignments($staff);
        $this->seedClientIssues($projects, $clients, $faker);
        $this->seedTodos($users, $faker);
        $this->seedCalendarEvents($users, $faker);
        $this->seedBookCalls($faker);
        $this->seedDigitalMarketingLeads($faker);
        $this->seedWebappLeads($faker);
        $this->seedCareerApplications($faker);
        $this->seedContactEnquiries($faker);
        $this->seedGoogleLeads($faker);
        $this->seedMetaLeads($faker);

        $this->command?->info('Added demo data without changing existing records. Demo account password: ' . self::PASSWORD);
    }

    private function seedUsers(string $role, int $count): array
    {
        $users = [];
        $password = Hash::make(self::PASSWORD);

        for ($index = 1; $index <= $count; $index++) {
            $email = self::PREFIX . ".{$role}." . str_pad((string) $index, 2, '0', STR_PAD_LEFT) . '@example.com';
            $user = User::withTrashed()->where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'first_name' => 'Demo ' . ucfirst($role),
                    'last_name' => str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'email' => $email,
                    'phone' => '90000' . str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                    'password' => $password,
                    'status' => 'active',
                    'role' => $role,
                ]);
            }

            $users[] = $user;
        }

        return $users;
    }

    private function seedUserDetails(array $staff, array $clients, $faker): void
    {
        foreach (array_merge($staff, $clients) as $user) {
            $this->insertOnce('user_address', ['user_id' => $user->id], [
                'address_line_1' => $faker->streetAddress(),
                'address_line_2' => null,
                'city' => $faker->city(),
                'state' => $faker->state(),
                'country' => 'India',
                'pincode' => $faker->numerify('######'),
            ]);
        }

        foreach ($clients as $clientIndex => $client) {
            for ($companyIndex = 1; $companyIndex <= 2; $companyIndex++) {
                $marker = str_pad((string) (($clientIndex * 2) + $companyIndex), 2, '0', STR_PAD_LEFT);
                $this->insertOnce('client_business_details', [
                    'user_id' => $client->id,
                    'industry' => self::PREFIX . '_industry_' . $marker,
                ], [
                    'client_type' => 'Company',
                    'website' => 'https://demo-' . $marker . '.example.com',
                    'company_name' => 'Demo Company ' . $marker,
                ]);
            }
        }
    }

    private function seedVendors($faker): array
    {
        $ids = [];
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $id = $this->insertOnce('vendors', ['email' => self::PREFIX . ".vendor.{$marker}@example.com"], [
                'name' => 'Demo Vendor ' . $marker,
                'phone' => $faker->numerify('9#########'),
                'address' => $faker->address(),
                'status' => 1,
            ]);
            if ($id) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function seedVendorServices(array $vendorIds, $faker): void
    {
        if ($vendorIds === []) {
            return;
        }

        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $start = Carbon::now()->subMonths($index + 2)->startOfMonth();
            $this->insertOnce('vendor_services', [
                'service_name' => self::PREFIX . ' Vendor Service ' . $marker,
            ], [
                'vendor_id' => $vendorIds[($index - 1) % count($vendorIds)],
                'service_details' => 'Seeded demo vendor service ' . $marker,
                'plan_type' => 'monthly',
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addYear()->toDateString(),
                'billing_date' => $start->copy()->addYear()->toDateString(),
                'status' => 'active',
            ]);
        }
    }

    private function seedServices(array $vendorIds, array $clients, $faker): void
    {
        if ($vendorIds === []) {
            return;
        }

        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $start = Carbon::now()->subMonths(1)->startOfMonth();
            $client = $clients[($index - 1) % count($clients)];
            $this->insertOnce('services', [
                'service_name' => self::PREFIX . ' Client Service ' . $marker,
            ], [
                'client_id' => $client->id,
                'vendor_id' => $vendorIds[($index - 1) % count($vendorIds)],
                'service_details' => 'Seeded demo client service ' . $marker,
                'plan_type' => 'monthly',
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addYear()->toDateString(),
                'billing_date' => $start->copy()->addYear()->toDateString(),
                'status' => 'active',
                'remark_text' => 'Demo seed record',
                'remark_color' => 'blue',
            ]);
        }
    }

    private function seedProjects(array $clients, array $staff, $faker): array
    {
        $ids = [];
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $id = $this->insertOnce('projects', [
                'project_name' => self::PREFIX . ' Project ' . $marker,
            ], [
                'project_code' => self::PREFIX . '_P' . $marker,
                'customer_id' => $clients[($index - 1) % count($clients)]->id,
                'status' => 'in_progress',
                'lifecycle_stage' => 'delivery',
                'start_date' => Carbon::now()->subDays($index)->toDateString(),
                'deadline' => Carbon::now()->addDays($index + 30)->toDateString(),
                'billing_type' => 'fixed_rate',
                'total_rate' => 25000 + ($index * 1250),
                'estimated_hours' => 80 + $index,
                'members' => json_encode([(string) $staff[($index - 1) % count($staff)]->id]),
                'description' => 'Seeded demo project ' . $marker,
                'priority' => 'medium',
                'technologies' => json_encode(['Laravel', 'MySQL']),
                'progress_percentage' => ($index * 4) % 100,
                'project_manager_id' => $staff[($index - 1) % count($staff)]->id,
                'health_status' => 'healthy',
                'last_activity_at' => now(),
            ]);
            if ($id) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function seedAmcServices(): void
    {
        if (! Schema::hasTable('amc_services') || ! Schema::hasTable('amc_service_details')) {
            return;
        }

        $services = DB::table('services')
            ->where('service_name', 'like', self::PREFIX . ' Client Service %')
            ->orderBy('service_name')
            ->get(['id']);

        foreach ($services as $index => $service) {
            $marker = $this->marker($index + 1);
            $start = Carbon::now()->startOfMonth();
            $amcId = $this->insertOnce('amc_services', ['service_id' => $service->id], [
                'total_visits' => 2,
                'amc_start_date' => $start->toDateString(),
                'amc_end_date' => $start->copy()->addYear()->toDateString(),
            ]);

            if ($amcId) {
                $this->insertOnce('amc_service_details', [
                    'amc_service_id' => $amcId,
                    'visit_number' => 1,
                ], [
                    'visit_date' => $start->copy()->addMonth()->toDateString(),
                    'status' => 'pending',
                    'details' => 'Seeded demo AMC visit ' . $marker,
                ]);
            }
        }
    }

    private function seedTasks(array $projectIds, array $staff, $faker): void
    {
        if ($projectIds === []) {
            return;
        }

        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $staffId = $staff[($index - 1) % count($staff)]->id;
            $this->insertOnce('tasks', [
                'title' => self::PREFIX . ' Task ' . $marker,
            ], [
                'task_code' => self::PREFIX . '_T' . $marker,
                'description' => 'Seeded demo task ' . $marker,
                'project_id' => $projectIds[($index - 1) % count($projectIds)],
                'followers' => json_encode([$staffId]),
                'assignees' => json_encode([$staffId]),
                'tags' => json_encode(['demo', 'seeded']),
                'status' => 'in_progress',
                'workflow_status' => 'in_progress',
                'priority' => 'medium',
                'start_date' => Carbon::now()->subDays($index)->toDateString(),
                'deadline' => Carbon::now()->addDays($index + 7)->toDateString(),
                'estimated_hours' => 8,
                'actual_hours' => 2,
            ]);
        }
    }

    private function seedLeads(array $staff, $faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $id = $this->insertOnce('leads', [
                'email' => self::PREFIX . ".lead.{$marker}@example.com",
            ], [
                'name' => 'Demo Lead ' . $marker,
                'phone' => $faker->numerify('9#########'),
                'company' => 'Demo Prospect Company ' . $marker,
                'company_name' => 'Demo Prospect Company ' . $marker,
                'position' => 'Operations Manager',
                'city' => $faker->city(),
                'state' => $faker->state(),
                'country' => 'India',
                'lead_code' => self::PREFIX . '_L' . $marker,
                'source' => 'Demo Seed',
                'priority' => 'medium',
                'expected_value' => 15000 + ($index * 500),
                'requirements' => 'Seeded demo lead ' . $marker,
                'created_by' => $staff[($index - 1) % count($staff)]->id,
                'status' => 'new',
                'description' => 'Seeded demo lead record ' . $marker,
            ]);

            if ($id) {
                $this->insertOnce('assigned_leads', ['lead_model' => 'lead', 'lead_id' => $id], [
                    'staff_ids' => json_encode([$staff[($index - 1) % count($staff)]->id]),
                ]);
            }
        }
    }

    private function seedLeadAssignments(array $staff): void
    {
        if (! Schema::hasTable('leads') || ! Schema::hasTable('assigned_leads')) {
            return;
        }

        $leads = DB::table('leads')
            ->where('email', 'like', self::PREFIX . '.lead.%@example.com')
            ->orderBy('id')
            ->get(['id', 'email']);

        foreach ($leads as $index => $lead) {
            $this->insertOnce('assigned_leads', ['lead_model' => 'lead', 'lead_id' => $lead->id], [
                'staff_ids' => json_encode([$staff[$index % count($staff)]->id]),
            ]);
        }
    }

    private function seedClientIssues(array $projects, array $clients, $faker): void
    {
        if ($projects === []) {
            return;
        }

        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('client_issues', [
                'issue_description' => self::PREFIX . ' Client issue ' . $marker,
            ], [
                'project_id' => $projects[($index - 1) % count($projects)],
                'customer_id' => $clients[($index - 1) % count($clients)]->id,
                'priority' => 'medium',
                'status' => 'open',
            ]);
        }
    }

    private function seedTodos(array $users, $faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $taskDate = Carbon::now()->addDays($index);
            $this->insertOnce('todos', [
                'title' => self::PREFIX . ' Follow-up ' . $marker,
                'user_id' => $users[($index - 1) % count($users)]->id,
            ], [
                'description' => 'Seeded demo follow-up ' . $marker,
                'task_date' => $taskDate->toDateString(),
                'task_time' => '10:00:00',
                'repeat_interval' => 1,
                'repeat_unit' => 'day',
                'starts_on' => $taskDate->toDateString(),
                'ends_type' => 'never',
                'is_completed' => false,
                'reminder_email' => false,
                'reminder_whatsapp' => false,
            ]);
        }
    }

    private function seedCalendarEvents(array $users, $faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $eventAt = Carbon::now()->subDays(2)->addHours($index);
            $this->insertOnce('calendar_events', [
                'title' => self::PREFIX . ' Review Meeting ' . $marker,
            ], [
                'description' => 'Seeded demo calendar event ' . $marker,
                'event_date' => $eventAt->toDateTimeString(),
                'event_time' => $eventAt->toDateTimeString(),
                'email_recipients' => $users[($index - 1) % count($users)]->email,
                'notification_sent' => true,
                'notification_sent_at' => now(),
                'reminder_10min_sent' => true,
                'reminder_10min_sent_at' => now(),
                'event_time_notification_sent' => true,
                'event_time_notification_sent_at' => now(),
                'created_by' => $users[($index - 1) % count($users)]->id,
                'status' => 1,
            ]);
        }
    }

    private function seedBookCalls($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $bookingAt = Carbon::now()->addDays($index)->setTime(10, 0);
            $this->insertOnce('bookcall', [
                'email' => self::PREFIX . ".call.{$marker}@example.com",
            ], [
                'name' => 'Demo Call Request ' . $marker,
                'phone' => $faker->numerify('9#########'),
                'meeting_agenda' => 'Seeded demo consultation ' . $marker,
                'booking_date' => $bookingAt->toDateString(),
                'booking_time' => $bookingAt->format('H:i:s'),
                'booking_datetime' => $bookingAt->toDateTimeString(),
            ]);
        }
    }

    private function seedDigitalMarketingLeads($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('digital_marketing_leads', [
                'email' => self::PREFIX . ".marketing.{$marker}@example.com",
            ], [
                'name' => 'Demo Marketing Lead ' . $marker,
                'phone' => $faker->numerify('9#########'),
                'company' => 'Demo Marketing Company ' . $marker,
                'website' => 'https://marketing-' . $marker . '.example.com',
                'source_page' => 'demo-seeder',
                'status' => 'new',
            ]);
        }
    }

    private function seedWebappLeads($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('webapp_leads', [
                'email' => self::PREFIX . ".webapp.{$marker}@example.com",
            ], [
                'name' => 'Demo Web Lead ' . $marker,
                'phone' => $faker->numerify('9#########'),
                'company' => 'Demo Web Company ' . $marker,
                'website' => 'https://web-' . $marker . '.example.com',
                'message' => 'Seeded demo web enquiry ' . $marker,
                'source_page' => 'demo-seeder',
                'status' => 'new',
            ]);
        }
    }

    private function seedCareerApplications($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('jobapplication', [
                'email' => self::PREFIX . ".career.{$marker}@example.com",
            ], [
                'fname' => 'Demo Candidate ' . $marker,
                'contact' => $faker->numerify('9#########'),
                'role' => 'Software Engineer',
                'experience' => '2 years',
                'ctc' => '500000',
                'ectc' => '650000',
                'location' => $faker->city(),
                'skills_text' => 'Laravel, PHP, MySQL',
                'skills_json' => json_encode(['Laravel', 'PHP', 'MySQL']),
                'ai_tools_text' => 'None',
                'ai_tools_json' => json_encode([]),
                'notice' => '30 days',
                'rn' => '',
                'refrence' => 'Demo seed',
                'resume_file' => 'demo-seeder/demo-resume-' . $marker . '.pdf',
                'portfolio_link' => '',
                'source_page' => 'demo-seeder',
            ]);
        }
    }

    private function seedContactEnquiries($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('contactform', [
                'email' => self::PREFIX . ".contact.{$marker}@example.com",
            ], [
                'fname' => 'Demo Contact ' . $marker,
                'lname' => 'Enquiry',
                'contact' => $faker->numerify('9#########'),
                'massage' => 'Seeded demo enquiry ' . $marker,
                'source_page' => 'demo-seeder',
            ]);
        }
    }

    private function seedGoogleLeads($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('google_leads', [
                'lead_id' => self::PREFIX . '_google_' . $marker,
            ], [
                'full_name' => 'Demo Google Lead ' . $marker,
                'email' => self::PREFIX . ".google.{$marker}@example.com",
                'phone' => $faker->numerify('9#########'),
                'company' => 'Demo Google Company ' . $marker,
                'is_test' => true,
                'lead_stage' => 'new',
                'status' => 'new',
                'raw_payload' => json_encode(['demo_seed' => true, 'record' => $marker]),
            ]);
        }
    }

    private function seedMetaLeads($faker): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $marker = $this->marker($index);
            $this->insertOnce('meta_leads', [
                'lead_id' => self::PREFIX . '_meta_' . $marker,
            ], [
                'form_id' => 'demo-form',
                'page_id' => 'demo-page',
                'full_name' => 'Demo Meta Lead ' . $marker,
                'email' => self::PREFIX . ".meta.{$marker}@example.com",
                'phone' => $faker->numerify('9#########'),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'field_data' => json_encode(['demo_seed' => true, 'record' => $marker]),
                'created_time' => now(),
                'status' => 'new',
                'welcome_whatsapp_status' => 'skipped',
            ]);
        }
    }

    private function insertOnce(string $table, array $identity, array $attributes): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $columns = array_flip(Schema::getColumnListing($table));
        $identity = array_intersect_key($identity, $columns);
        $attributes = array_intersect_key($attributes, $columns);

        if ($identity === []) {
            return null;
        }

        $query = DB::table($table);
        foreach ($identity as $column => $value) {
            $query->where($column, $value);
        }

        $existing = $query->first();
        if ($existing) {
            return isset($existing->id) ? (int) $existing->id : null;
        }

        $record = array_merge($identity, $attributes);
        if (isset($columns['created_at']) && ! isset($record['created_at'])) {
            $record['created_at'] = now();
        }
        if (isset($columns['updated_at']) && ! isset($record['updated_at'])) {
            $record['updated_at'] = now();
        }

        return (int) DB::table($table)->insertGetId($record);
    }

    private function marker(int $index): string
    {
        return str_pad((string) $index, 2, '0', STR_PAD_LEFT);
    }
}
