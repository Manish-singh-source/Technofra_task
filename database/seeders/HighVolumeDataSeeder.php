<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HighVolumeDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create();
        $now = now();
        $seedKey = 'hv' . $now->format('YmdHis');

        DB::disableQueryLog();

        if (! Schema::hasTable('users')) {
            return;
        }

        $staffIds = $this->seedUsers($faker, $seedKey, 'staff', 120);
        $clientIds = $this->seedUsers($faker, $seedKey, 'client', 260);
        $allUserIds = array_merge($staffIds, $clientIds);

        $teamIds = $this->seedTeams($faker);
        $departmentIds = $this->seedDepartments($faker);

        $this->seedStaffPivots($staffIds, $teamIds, $departmentIds, $now);
        $this->seedUserAddress($allUserIds, $faker, $now);
        $this->seedClientBusinessDetails($clientIds, $faker, $now);

        $vendorIds = $this->seedVendors($faker, $seedKey, 120, $now);
        $this->seedVendorServices($faker, $vendorIds, 420, $now);
        $serviceIds = $this->seedServices($faker, $clientIds, $vendorIds, 700, $now);

        $projectIds = $this->seedProjects($faker, $clientIds, $staffIds, 500, $now);
        $taskIds = $this->seedTasks($faker, $projectIds, $staffIds, 900, $now);

        $this->seedTaskAttachments($faker, $taskIds, 450, $now);
        $this->seedTaskComments($faker, $taskIds, $allUserIds, 900, $now);
        $this->seedProjectComments($faker, $projectIds, $allUserIds, 600, $now);
        $this->seedProjectStatusLogs($faker, $projectIds, 500, $now);
        $this->seedProjectMilestones($faker, $projectIds, 700, $now);
        $this->seedProjectFiles($faker, $projectIds, $staffIds, 500, $now);
        $this->seedProjectIssues($faker, $projectIds, $clientIds, 450, $now);
        $clientIssueIds = $this->seedClientIssues($faker, $projectIds, $clientIds, 450, $now);
        $this->seedClientIssueTasks($faker, $clientIssueIds, 800, $now);
        $this->seedClientIssueTeamAssignments($faker, $clientIssueIds, $staffIds, 500, $now);

        $this->seedLeads($faker, $staffIds, 600, $now);
        $this->seedDigitalMarketingLeads($faker, 350);
        $this->seedWebappLeads($faker, 350);
        $this->seedBookCalls($faker, 300);
        $this->seedCalendarEvents($faker, $allUserIds, 450, $now);
        $this->seedTodos($faker, $allUserIds, 900, $now);
        $this->seedFcmTokens($faker, $allUserIds, 450, $now);
        $this->seedNotificationReads($faker, $allUserIds, $serviceIds, 450, $now);
        $this->seedNotifications($faker, $allUserIds, 400, $now);

        $this->command?->info('High-volume relational dataset generated successfully.');
    }

    private function seedUsers($faker, string $seedKey, string $role, int $count): array
    {
        $prefix = "{$seedKey}_{$role}_";
        $rows = [];
        $password = Hash::make('password');

        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $prefix . $i . '@example.com',
                'email_verified_at' => now()->subDays(random_int(0, 365)),
                'phone' => $faker->numerify('9#########'),
                'password' => $password,
                'profile_image' => null,
                'status' => $faker->randomElement(['active', 'inactive']),
                'role' => $role,
                'remember_token' => Str::random(10),
                'created_at' => now()->subDays(random_int(0, 720)),
                'updated_at' => now()->subDays(random_int(0, 180)),
            ];
        }

        $this->chunkInsert('users', $rows, 1000);

        return DB::table('users')
            ->where('email', 'like', $prefix . '%')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function seedTeams($faker): array
    {
        if (! Schema::hasTable('teams')) {
            return [];
        }

        $teams = ['Support', 'Development', 'Design', 'QA', 'DevOps', 'Marketing', 'Sales', 'Accounts'];
        $rows = [];
        foreach ($teams as $name) {
            $rows[] = [
                'name' => $name . ' Team',
                'description' => $faker->sentence(10),
                'icon_path' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('teams')->upsert($rows, ['name'], ['description', 'icon_path', 'is_active', 'updated_at']);

        return DB::table('teams')->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedDepartments($faker): array
    {
        if (! Schema::hasTable('departments')) {
            return [];
        }

        $departments = ['Engineering', 'Operations', 'Product', 'HR', 'Finance', 'Customer Success'];
        $rows = [];
        foreach ($departments as $name) {
            $rows[] = [
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('departments')->upsert($rows, ['name'], ['is_active', 'updated_at']);

        return DB::table('departments')->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedStaffPivots(array $staffIds, array $teamIds, array $departmentIds, Carbon $now): void
    {
        if (Schema::hasTable('staff_team') && $teamIds !== []) {
            $rows = [];
            foreach ($staffIds as $staffId) {
                $picked = array_rand(array_flip($teamIds), min(count($teamIds), random_int(1, 2)));
                $picked = is_array($picked) ? $picked : [$picked];
                foreach ($picked as $teamId) {
                    $rows[] = [
                        'user_id' => $staffId,
                        'team_id' => $teamId,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ];
                }
            }
            $this->chunkInsert('staff_team', $rows);
        }

        if (Schema::hasTable('staff_department') && $departmentIds !== []) {
            $rows = [];
            foreach ($staffIds as $staffId) {
                $picked = array_rand(array_flip($departmentIds), min(count($departmentIds), random_int(1, 2)));
                $picked = is_array($picked) ? $picked : [$picked];
                foreach ($picked as $departmentId) {
                    $rows[] = [
                        'user_id' => $staffId,
                        'department_id' => $departmentId,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ];
                }
            }
            $this->chunkInsert('staff_department', $rows);
        }
    }

    private function seedUserAddress(array $userIds, $faker, Carbon $now): void
    {
        if (! Schema::hasTable('user_address')) {
            return;
        }

        $rows = [];
        foreach ($userIds as $userId) {
            $rows[] = [
                'user_id' => $userId,
                'address_line_1' => $faker->streetAddress(),
                'address_line_2' => $faker->boolean(35) ? $faker->secondaryAddress() : null,
                'city' => $faker->city(),
                'state' => $faker->state(),
                'country' => 'India',
                'pincode' => $faker->postcode(),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }
        $this->chunkInsert('user_address', $rows);
    }

    private function seedClientBusinessDetails(array $clientIds, $faker, Carbon $now): void
    {
        if (! Schema::hasTable('client_business_details')) {
            return;
        }

        $types = ['sole_proprietorship', 'partnership', 'pvt_ltd', 'llp'];
        $rows = [];
        foreach ($clientIds as $clientId) {
            $rows[] = [
                'user_id' => $clientId,
                'client_type' => $faker->randomElement($types),
                'industry' => $faker->optional()->companySuffix(),
                'website' => $faker->optional()->url(),
                'company_name' => $faker->company(),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }
        $this->chunkInsert('client_business_details', $rows);
    }

    private function seedVendors($faker, string $seedKey, int $count, Carbon $now): array
    {
        if (! Schema::hasTable('vendors')) {
            return [];
        }

        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'name' => $faker->company(),
                'email' => "{$seedKey}_vendor_{$i}@example.com",
                'phone' => $faker->numerify('9#########'),
                'address' => $faker->address(),
                'status' => $faker->randomElement(['active', 'inactive']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('vendors', $rows);

        return DB::table('vendors')
            ->where('email', 'like', "{$seedKey}_vendor_%")
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function seedVendorServices($faker, array $vendorIds, int $count, Carbon $now): array
    {
        if (! Schema::hasTable('vendor_services') || $vendorIds === []) {
            return [];
        }

        $rows = [];
        $statuses = ['active', 'inactive', 'expired', 'pending'];
        $planTypes = ['monthly', 'yearly', 'quarterly'];

        for ($i = 0; $i < $count; $i++) {
            $start = $faker->dateTimeBetween('-2 years', '+2 months');
            $end = Carbon::instance($start)->addDays(random_int(30, 540));
            $rows[] = [
                'vendor_id' => $faker->randomElement($vendorIds),
                'service_name' => ucfirst($faker->word()) . ' Plan',
                'service_details' => $faker->sentence(12),
                'plan_type' => $faker->randomElement($planTypes),
                'start_date' => Carbon::instance($start)->toDateString(),
                'end_date' => $end->toDateString(),
                'billing_date' => $faker->boolean(90) ? $end->copy()->subDays(random_int(0, 20))->toDateString() : null,
                'status' => $faker->randomElement($statuses),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => $faker->boolean(4) ? $faker->dateTimeBetween('-6 months', 'now') : null,
            ];
        }
        $this->chunkInsert('vendor_services', $rows);

        return DB::table('vendor_services')->orderByDesc('id')->limit($count)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedServices($faker, array $clientIds, array $vendorIds, int $count, Carbon $now): array
    {
        if (! Schema::hasTable('services') || $vendorIds === []) {
            return [];
        }

        $rows = [];
        $statuses = ['active', 'inactive', 'expired', 'pending'];
        $remarks = ['Urgent', 'Follow up', 'Auto-renew enabled', 'Client requested hold', 'Awaiting documents'];
        $colors = ['red', 'green', 'blue', 'orange', 'purple', 'gray'];

        for ($i = 0; $i < $count; $i++) {
            $start = $faker->dateTimeBetween('-3 years', '+3 months');
            $end = Carbon::instance($start)->addDays(random_int(15, 720));
            $status = $end->isPast() ? $faker->randomElement(['expired', 'inactive']) : $faker->randomElement($statuses);

            $rows[] = [
                'client_id' => $faker->boolean(92) && $clientIds !== [] ? $faker->randomElement($clientIds) : null,
                'vendor_id' => $faker->randomElement($vendorIds),
                'service_name' => ucfirst($faker->word()) . ' Service',
                'service_details' => $faker->sentence(15),
                'start_date' => Carbon::instance($start)->toDateString(),
                'end_date' => $end->toDateString(),
                'billing_date' => $faker->boolean(96) ? $end->copy()->subDays(random_int(0, 25))->toDateString() : $end->toDateString(),
                'status' => $status,
                'remark_text' => $faker->boolean(60) ? $faker->randomElement($remarks) : null,
                'remark_color' => $faker->boolean(60) ? $faker->randomElement($colors) : null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => $faker->boolean(3) ? $faker->dateTimeBetween('-1 year', 'now') : null,
            ];
        }

        $this->chunkInsert('services', $rows);

        return DB::table('services')->orderByDesc('id')->limit($count)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedProjects($faker, array $clientIds, array $staffIds, int $count, Carbon $now): array
    {
        if (! Schema::hasTable('projects')) {
            return [];
        }

        $rows = [];
        $statuses = ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high'];
        $billingTypes = ['fixed_rate', 'hourly_rate'];
        $techStack = ['Laravel', 'React', 'Vue', 'Node', 'MySQL', 'Redis', 'Docker', 'AWS'];
        $tagPool = ['web', 'mobile', 'support', 'branding', 'maintenance', 'enterprise'];

        for ($i = 0; $i < $count; $i++) {
            $start = Carbon::instance($faker->dateTimeBetween('-2 years', '+4 months'));
            $deadline = $faker->boolean(90) ? $start->copy()->addDays(random_int(20, 240)) : null;
            $memberCount = min(max(count($staffIds), 1), random_int(2, 6));
            $members = $staffIds !== [] ? array_values(array_map('strval', (array) array_rand(array_flip($staffIds), $memberCount))) : [];
            $techCount = random_int(1, 4);
            $tagCount = random_int(1, 3);

            $rows[] = [
                'project_name' => 'Project ' . strtoupper(Str::random(8)),
                'customer_id' => $faker->boolean(90) && $clientIds !== [] ? $faker->randomElement($clientIds) : null,
                'status' => $faker->randomElement($statuses),
                'start_date' => $start->toDateString(),
                'deadline' => $deadline?->toDateString(),
                'billing_type' => $faker->randomElement($billingTypes),
                'total_rate' => $faker->randomFloat(2, 15000, 500000),
                'estimated_hours' => random_int(20, 2000),
                'tags' => json_encode($faker->randomElements($tagPool, $tagCount)),
                'members' => json_encode($members),
                'description' => $faker->paragraph(),
                'priority' => $faker->randomElement($priorities),
                'technologies' => json_encode($faker->randomElements($techStack, $techCount)),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => $faker->boolean(2) ? $faker->dateTimeBetween('-6 months', 'now') : null,
            ];
        }

        $this->chunkInsert('projects', $rows);

        return DB::table('projects')->orderByDesc('id')->limit($count)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedTasks($faker, array $projectIds, array $staffIds, int $count, Carbon $now): array
    {
        if (! Schema::hasTable('tasks')) {
            return [];
        }

        $rows = [];
        $statuses = ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high'];
        $tags = ['bug', 'feature', 'urgent', 'ui', 'api', 'ops'];

        for ($i = 0; $i < $count; $i++) {
            $start = Carbon::instance($faker->dateTimeBetween('-18 months', '+2 months'));
            $deadline = $faker->boolean(85) ? $start->copy()->addDays(random_int(1, 90)) : null;
            $assignees = $staffIds !== [] ? $faker->randomElements($staffIds, random_int(1, min(3, count($staffIds)))) : [];
            $followers = $staffIds !== [] ? $faker->randomElements($staffIds, random_int(1, min(5, count($staffIds)))) : [];
            $projectId = $projectIds !== [] && $faker->boolean(94) ? $faker->randomElement($projectIds) : null;

            $rows[] = [
                'title' => ucfirst($faker->words(random_int(2, 5), true)),
                'description' => $faker->paragraph(),
                'project_id' => $projectId,
                'followers' => json_encode(array_values($followers)),
                'assignees' => json_encode(array_values($assignees)),
                'tags' => json_encode($faker->randomElements($tags, random_int(1, 3))),
                'status' => $faker->randomElement($statuses),
                'priority' => $faker->randomElement($priorities),
                'start_date' => $start->toDateString(),
                'deadline' => $deadline?->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => $faker->boolean(2) ? $faker->dateTimeBetween('-4 months', 'now') : null,
            ];
        }

        $this->chunkInsert('tasks', $rows);

        return DB::table('tasks')->orderByDesc('id')->limit($count)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedTaskAttachments($faker, array $taskIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('task_attachments') || $taskIds === []) {
            return;
        }

        $rows = [];
        $types = ['pdf', 'jpg', 'png', 'docx', 'xlsx'];
        for ($i = 0; $i < $count; $i++) {
            $type = $faker->randomElement($types);
            $file = Str::uuid() . '.' . $type;
            $rows[] = [
                'task_id' => $faker->randomElement($taskIds),
                'file_name' => $file,
                'file_path' => 'uploads/tasks/' . $file,
                'file_type' => $type,
                'file_size' => random_int(10000, 5000000),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('task_attachments', $rows);
    }

    private function seedTaskComments($faker, array $taskIds, array $userIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('task_comments') || $taskIds === [] || $userIds === []) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'task_id' => $faker->randomElement($taskIds),
                'user_id' => $faker->randomElement($userIds),
                'comment' => $faker->sentence(random_int(6, 18)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('task_comments', $rows);
    }

    private function seedProjectComments($faker, array $projectIds, array $userIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('project_comments') || $projectIds === [] || $userIds === []) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'project_id' => $faker->randomElement($projectIds),
                'user_id' => $faker->randomElement($userIds),
                'comment' => $faker->sentence(random_int(6, 15)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('project_comments', $rows);
    }

    private function seedProjectStatusLogs($faker, array $projectIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('project_status_logs') || $projectIds === []) {
            return;
        }

        $rows = [];
        $statuses = ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        for ($i = 0; $i < $count; $i++) {
            $startedAt = Carbon::instance($faker->dateTimeBetween('-2 years', 'now'));
            $endedAt = $faker->boolean(65) ? $startedAt->copy()->addDays(random_int(1, 120)) : null;
            $rows[] = [
                'project_id' => $faker->randomElement($projectIds),
                'status' => $faker->randomElement($statuses),
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('project_status_logs', $rows);
    }

    private function seedProjectMilestones($faker, array $projectIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('project_milestones') || $projectIds === []) {
            return;
        }

        $rows = [];
        $statuses = ['pending', 'in_progress', 'completed'];
        for ($i = 0; $i < $count; $i++) {
            $status = $faker->randomElement($statuses);
            $rows[] = [
                'project_id' => $faker->randomElement($projectIds),
                'title' => 'Milestone ' . strtoupper(Str::random(5)),
                'description' => $faker->sentence(15),
                'status' => $status,
                'due_date' => $faker->dateTimeBetween('-6 months', '+8 months'),
                'completed_at' => $status === 'completed' ? $faker->dateTimeBetween('-6 months', 'now') : null,
                'sort_order' => random_int(0, 15),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('project_milestones', $rows);
    }

    private function seedProjectFiles($faker, array $projectIds, array $staffIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('project_files') || $projectIds === []) {
            return;
        }

        $rows = [];
        $types = ['pdf', 'jpg', 'png', 'docx', 'xlsx', 'zip'];
        for ($i = 0; $i < $count; $i++) {
            $type = $faker->randomElement($types);
            $original = Str::slug($faker->words(3, true)) . '.' . $type;
            $stored = Str::uuid() . '.' . $type;
            $rows[] = [
                'project_id' => $faker->randomElement($projectIds),
                'file_name' => $stored,
                'original_name' => $original,
                'file_type' => $type,
                'file_size' => (string) random_int(10000, 25000000),
                'file_path' => 'uploads/projects/' . $stored,
                'description' => $faker->optional()->sentence(10),
                'uploaded_by' => $staffIds !== [] ? $faker->randomElement($staffIds) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('project_files', $rows);
    }

    private function seedProjectIssues($faker, array $projectIds, array $clientIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('project_issues') || $projectIds === [] || $clientIds === []) {
            return;
        }

        $rows = [];
        $priorities = ['low', 'medium', 'high'];
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'project_id' => $faker->randomElement($projectIds),
                'customer_id' => $faker->randomElement($clientIds),
                'issue_description' => $faker->paragraph(),
                'priority' => $faker->randomElement($priorities),
                'status' => $faker->randomElement($statuses),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('project_issues', $rows);
    }

    private function seedClientIssues($faker, array $projectIds, array $clientIds, int $count, Carbon $now): array
    {
        if (! Schema::hasTable('client_issues') || $projectIds === [] || $clientIds === []) {
            return [];
        }

        $rows = [];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'project_id' => $faker->randomElement($projectIds),
                'customer_id' => $faker->randomElement($clientIds),
                'issue_description' => $faker->paragraph(),
                'priority' => $faker->randomElement($priorities),
                'status' => $faker->randomElement($statuses),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('client_issues', $rows);

        return DB::table('client_issues')->orderByDesc('id')->limit($count)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function seedClientIssueTasks($faker, array $clientIssueIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('client_issue_tasks') || $clientIssueIds === []) {
            return;
        }

        $rows = [];
        $statuses = ['todo', 'in_progress', 'review', 'done'];
        for ($i = 0; $i < $count; $i++) {
            $startDate = Carbon::instance($faker->dateTimeBetween('-6 months', '+4 months'));
            $rows[] = [
                'client_issue_id' => $faker->randomElement($clientIssueIds),
                'title' => ucfirst($faker->words(4, true)),
                'description' => $faker->optional()->sentence(16),
                'status' => $faker->randomElement($statuses),
                'priority' => $faker->randomElement(['low', 'medium', 'high']),
                'assigned_to' => null,
                'start_date' => $startDate->toDateString(),
                'due_time' => $faker->time(),
                'reminder_date' => $faker->boolean(50) ? $startDate->copy()->addDays(random_int(0, 15))->toDateString() : null,
                'reminder_time' => $faker->boolean(50) ? $faker->time() : null,
                'checklist_data' => json_encode([
                    ['label' => 'Initial assessment', 'done' => $faker->boolean()],
                    ['label' => 'Client update', 'done' => $faker->boolean()],
                ]),
                'labels_data' => json_encode($faker->randomElements(['bug', 'ui', 'backend', 'infra'], random_int(1, 3))),
                'attachment' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('client_issue_tasks', $rows);
    }

    private function seedClientIssueTeamAssignments($faker, array $clientIssueIds, array $staffIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('client_issue_team_assignments') || $clientIssueIds === []) {
            return;
        }

        $teams = ['Support Team', 'Graphic Team', 'Digital Marketing Team', 'Account Team', 'Development Team'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'client_issue_id' => $faker->randomElement($clientIssueIds),
                'team_name' => $faker->randomElement($teams),
                'assigned_to' => $staffIds !== [] ? $faker->randomElement($staffIds) : null,
                'note' => $faker->optional()->sentence(),
                'assigned_by' => $staffIds !== [] ? $faker->randomElement($staffIds) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->chunkInsert('client_issue_team_assignments', $rows);
    }

    private function seedLeads($faker, array $staffIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        $rows = [];
        $statuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
        $sources = ['Website', 'Referral', 'LinkedIn', 'Facebook', 'Google Ads', 'Cold Call'];
        for ($i = 0; $i < $count; $i++) {
            $assigned = $staffIds !== [] ? $faker->randomElements($staffIds, random_int(1, min(3, count($staffIds)))) : [];
            $rows[] = [
                'name' => $faker->name(),
                'email' => $faker->optional()->safeEmail(),
                'phone' => $faker->numerify('9#########'),
                'company' => $faker->company(),
                'position' => $faker->jobTitle(),
                'website' => $faker->optional()->url(),
                'address' => $faker->optional()->address(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'country' => 'India',
                'zipCode' => $faker->postcode(),
                'lead_value' => $faker->randomFloat(2, 5000, 500000),
                'source' => $faker->randomElement($sources),
                'assigned' => json_encode(array_values($assigned)),
                'tags' => json_encode($faker->randomElements(['hot', 'cold', 'enterprise', 'startup'], random_int(1, 3))),
                'description' => $faker->optional()->sentence(16),
                'status' => $faker->randomElement($statuses),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('leads', $rows);
    }

    private function seedDigitalMarketingLeads($faker, int $count): void
    {
        if (! Schema::hasTable('digital_marketing_leads')) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'name' => $faker->name(),
                'email' => $faker->safeEmail(),
                'phone' => $faker->numerify('9#########'),
                'company' => $faker->company(),
                'website' => $faker->url(),
                'source_page' => 'digitalmarketingad.php',
                'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
            ];
        }
        $this->chunkInsert('digital_marketing_leads', $rows);
    }

    private function seedWebappLeads($faker, int $count): void
    {
        if (! Schema::hasTable('webapp_leads')) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'name' => $faker->name(),
                'email' => $faker->safeEmail(),
                'phone' => $faker->numerify('9#########'),
                'company' => $faker->company(),
                'website' => $faker->url(),
                'message' => $faker->optional()->sentence(18),
                'source_page' => 'webapp.php',
                'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
            ];
        }
        $this->chunkInsert('webapp_leads', $rows);
    }

    private function seedBookCalls($faker, int $count): void
    {
        if (! Schema::hasTable('bookcall')) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $bookingDateTime = Carbon::instance($faker->dateTimeBetween('-12 months', '+6 months'));
            $rows[] = [
                'name' => $faker->name(),
                'email' => $faker->safeEmail(),
                'phone' => $faker->numerify('9#########'),
                'meeting_agenda' => $faker->optional()->sentence(12),
                'booking_date' => $bookingDateTime->toDateString(),
                'booking_time' => $bookingDateTime->format('H:i:s'),
                'booking_datetime' => $bookingDateTime->toDateTimeString(),
                'created_at' => $faker->dateTimeBetween('-12 months', 'now'),
            ];
        }
        $this->chunkInsert('bookcall', $rows);
    }

    private function seedCalendarEvents($faker, array $userIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('calendar_events') || $userIds === []) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $eventDate = Carbon::instance($faker->dateTimeBetween('-6 months', '+6 months'));
            $emailCount = random_int(1, 3);
            $emails = [];
            for ($j = 0; $j < $emailCount; $j++) {
                $emails[] = $faker->safeEmail();
            }

            $rows[] = [
                'title' => ucfirst($faker->words(4, true)),
                'description' => $faker->optional()->sentence(16),
                'event_date' => $eventDate->copy()->toDateTimeString(),
                'event_time' => $eventDate->copy()->toDateTimeString(),
                'email_recipients' => implode(',', $emails),
                'whatsapp_recipients' => $faker->boolean(55) ? implode(',', [$faker->numerify('9#########'), $faker->numerify('9#########')]) : null,
                'notification_sent' => $faker->boolean(60),
                'notification_sent_at' => $faker->boolean(60) ? $eventDate->copy()->subMinutes(30)->toDateTimeString() : null,
                'reminder_10min_sent' => $faker->boolean(50),
                'reminder_10min_sent_at' => $faker->boolean(50) ? $eventDate->copy()->subMinutes(10)->toDateTimeString() : null,
                'event_time_notification_sent' => $faker->boolean(45),
                'event_time_notification_sent_at' => $faker->boolean(45) ? $eventDate->copy()->toDateTimeString() : null,
                'created_by' => $faker->randomElement($userIds),
                'status' => $faker->boolean(90),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('calendar_events', $rows);
    }

    private function seedTodos($faker, array $userIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('todos') || $userIds === []) {
            return;
        }

        $rows = [];
        $repeatUnits = ['day', 'week', 'month', 'year'];
        $endsType = ['never', 'on', 'after'];

        for ($i = 0; $i < $count; $i++) {
            $taskDate = Carbon::instance($faker->dateTimeBetween('-4 months', '+4 months'));
            $isCompleted = $faker->boolean(45);
            $ends = $faker->randomElement($endsType);
            $rows[] = [
                'user_id' => $faker->randomElement($userIds),
                'title' => ucfirst($faker->words(random_int(2, 6), true)),
                'description' => $faker->optional()->sentence(16),
                'attachments' => $faker->boolean(30) ? json_encode([Str::uuid() . '.pdf']) : null,
                'task_date' => $taskDate->toDateString(),
                'task_time' => $faker->boolean(70) ? $faker->time() : null,
                'repeat_interval' => random_int(1, 6),
                'repeat_unit' => $faker->randomElement($repeatUnits),
                'repeat_days' => $faker->boolean(35) ? json_encode($faker->randomElements(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], random_int(1, 4))) : null,
                'reminder_time' => $faker->boolean(55) ? $faker->time() : null,
                'starts_on' => $taskDate->copy()->subDays(random_int(0, 7))->toDateString(),
                'ends_type' => $ends,
                'ends_on' => $ends === 'on' ? $taskDate->copy()->addDays(random_int(7, 120))->toDateString() : null,
                'ends_after_occurrences' => $ends === 'after' ? random_int(2, 20) : null,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? $taskDate->copy()->addHours(random_int(1, 36)) : null,
                'last_reminded_occurrence_on' => $faker->boolean(30) ? $taskDate->toDateString() : null,
                'last_reminder_sent_at' => $faker->boolean(30) ? $taskDate->copy()->subHours(1) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->chunkInsert('todos', $rows);
    }

    private function seedFcmTokens($faker, array $userIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('fcm_tokens') || $userIds === []) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $userId = $faker->randomElement($userIds);
            $rows[] = [
                'user_id' => $userId,
                'token' => hash('sha256', $userId . '_' . Str::uuid()),
                'device_id' => (string) Str::uuid(),
                'platform' => $faker->randomElement(['android', 'ios', 'web']),
                'is_active' => $faker->boolean(85),
                'last_used_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('fcm_tokens', $rows);
    }

    private function seedNotificationReads($faker, array $userIds, array $serviceIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('notification_reads') || $userIds === [] || $serviceIds === []) {
            return;
        }

        $types = ['expired', 'expiring_today', 'expiring_tomorrow', 'expiring_soon'];
        $used = [];
        $rows = [];

        while (count($rows) < $count) {
            $userId = $faker->randomElement($userIds);
            $serviceId = $faker->randomElement($serviceIds);
            $type = $faker->randomElement($types);
            $key = $userId . '_' . $serviceId . '_' . $type;
            if (isset($used[$key])) {
                continue;
            }
            $used[$key] = true;

            $rows[] = [
                'user_id' => $userId,
                'service_id' => $serviceId,
                'notification_type' => $type,
                'read_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->chunkInsert('notification_reads', $rows);
    }

    private function seedNotifications($faker, array $userIds, int $count, Carbon $now): void
    {
        if (! Schema::hasTable('notifications') || $userIds === []) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\ServiceReminderNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $faker->randomElement($userIds),
                'data' => json_encode([
                    'title' => 'Service Reminder',
                    'message' => $faker->sentence(10),
                ]),
                'read_at' => $faker->boolean(60) ? $faker->dateTimeBetween('-3 months', 'now') : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->chunkInsert('notifications', $rows);
    }

    private function chunkInsert(string $table, array $rows, int $chunkSize = 1000): void
    {
        if (! Schema::hasTable($table) || $rows === []) {
            return;
        }

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
