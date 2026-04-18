<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $staffMap = $this->staffUserMap();
        $customerMap = $this->customerUserMap();
        $clientMap = $this->clientUserMap();

        $this->migrateJsonAssignments($staffMap);
        $this->migrateProjectCustomerIds($customerMap);
        $this->migrateIssueCustomerIds('client_issues', $customerMap);
        $this->migrateIssueCustomerIds('project_issues', $customerMap);
        $this->migrateServiceClientIds($clientMap);
        $this->migrateClientIssueAssignedTo($staffMap);

        $this->syncServiceClientForeignKey();
        $this->syncProjectCustomerForeignKey();
        $this->syncIssueCustomerForeignKey('client_issues');
        $this->syncIssueCustomerForeignKey('project_issues');
        $this->syncAssignedToForeignKey();
    }

    public function down(): void
    {
        // This migration remaps live foreign keys and JSON id arrays from legacy tables to users.
        // Reversing it safely is not practical without a preserved legacy-id snapshot.
    }

    private function staffUserMap(): array
    {
        if (!Schema::hasTable('staff') || !Schema::hasColumn('staff', 'user_id')) {
            return [];
        }

        return DB::table('staff')
            ->whereNotNull('user_id')
            ->pluck('user_id', 'id')
            ->map(fn ($userId) => (int) $userId)
            ->all();
    }

    private function customerUserMap(): array
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'user_id')) {
            return [];
        }

        return DB::table('customers')
            ->whereNotNull('user_id')
            ->pluck('user_id', 'id')
            ->map(fn ($userId) => (int) $userId)
            ->all();
    }

    private function clientUserMap(): array
    {
        if (!Schema::hasTable('clients')) {
            return [];
        }

        return DB::table('clients')
            ->join('users', function ($join) {
                $join->on('users.email', '=', 'clients.email')
                    ->where('users.role', '=', 'client');
            })
            ->pluck('users.id', 'clients.id')
            ->map(fn ($userId) => (int) $userId)
            ->all();
    }

    private function migrateJsonAssignments(array $staffMap): void
    {
        $this->migrateJsonIdColumn('projects', 'members', $staffMap);
        $this->migrateJsonIdColumn('tasks', 'assignees', $staffMap);
        $this->migrateJsonIdColumn('tasks', 'followers', $staffMap);
        $this->migrateJsonIdColumn('leads', 'assigned', $staffMap);
    }

    private function migrateJsonIdColumn(string $table, string $column, array $legacyToUserMap): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->select('id', $column)
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($table, $column, $legacyToUserMap) {
                $currentIds = json_decode((string) $row->{$column}, true);

                if (!is_array($currentIds)) {
                    return;
                }

                $mappedIds = collect($currentIds)
                    ->map(function ($legacyId) use ($legacyToUserMap) {
                        $legacyId = (int) $legacyId;

                        if ($legacyId <= 0) {
                            return null;
                        }

                        if (isset($legacyToUserMap[$legacyId])) {
                            return (int) $legacyToUserMap[$legacyId];
                        }

                        return $this->userExists($legacyId) ? $legacyId : null;
                    })
                    ->filter(fn ($value) => $value !== null)
                    ->unique()
                    ->values()
                    ->all();

                DB::table($table)
                    ->where('id', $row->id)
                    ->update([$column => empty($mappedIds) ? null : json_encode($mappedIds)]);
            });
    }

    private function migrateProjectCustomerIds(array $customerMap): void
    {
        if (!Schema::hasTable('projects') || !Schema::hasColumn('projects', 'customer_id')) {
            return;
        }

        DB::table('projects')
            ->select('id', 'customer_id')
            ->orderBy('id')
            ->get()
            ->each(function ($project) use ($customerMap) {
                $resolvedId = $this->resolveUserId($project->customer_id, $customerMap);

                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['customer_id' => $resolvedId]);
            });
    }

    private function migrateIssueCustomerIds(string $table, array $customerMap): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'customer_id')) {
            return;
        }

        DB::table($table)
            ->select('id', 'customer_id')
            ->orderBy('id')
            ->get()
            ->each(function ($issue) use ($table, $customerMap) {
                $resolvedId = $this->resolveUserId($issue->customer_id, $customerMap);

                DB::table($table)
                    ->where('id', $issue->id)
                    ->update(['customer_id' => $resolvedId]);
            });
    }

    private function migrateServiceClientIds(array $clientMap): void
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'client_id')) {
            return;
        }

        DB::table('services')
            ->select('id', 'client_id')
            ->orderBy('id')
            ->get()
            ->each(function ($service) use ($clientMap) {
                $resolvedId = $this->resolveUserId($service->client_id, $clientMap);

                DB::table('services')
                    ->where('id', $service->id)
                    ->update(['client_id' => $resolvedId]);
            });
    }

    private function migrateClientIssueAssignedTo(array $staffMap): void
    {
        if (!Schema::hasTable('client_issue_team_assignments') || !Schema::hasColumn('client_issue_team_assignments', 'assigned_to')) {
            return;
        }

        DB::table('client_issue_team_assignments')
            ->select('id', 'assigned_to')
            ->orderBy('id')
            ->get()
            ->each(function ($assignment) use ($staffMap) {
                if ($assignment->assigned_to === null || trim((string) $assignment->assigned_to) === '') {
                    return;
                }

                $resolvedId = is_numeric($assignment->assigned_to)
                    ? $this->resolveUserId((int) $assignment->assigned_to, $staffMap)
                    : null;

                DB::table('client_issue_team_assignments')
                    ->where('id', $assignment->id)
                    ->update(['assigned_to' => $resolvedId]);
            });
    }

    private function syncServiceClientForeignKey(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'client_id')) {
            return;
        }

        $this->dropForeignIfExists('services', 'client_id');

        DB::statement('ALTER TABLE services MODIFY client_id BIGINT UNSIGNED NULL');

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function syncProjectCustomerForeignKey(): void
    {
        if (!Schema::hasTable('projects') || !Schema::hasColumn('projects', 'customer_id')) {
            return;
        }

        $this->dropForeignIfExists('projects', 'customer_id');

        DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NULL');

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function syncIssueCustomerForeignKey(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'customer_id')) {
            return;
        }

        $this->dropForeignIfExists($table, 'customer_id');

        DB::statement("ALTER TABLE {$table} MODIFY customer_id BIGINT UNSIGNED NULL");

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function syncAssignedToForeignKey(): void
    {
        if (!Schema::hasTable('client_issue_team_assignments') || !Schema::hasColumn('client_issue_team_assignments', 'assigned_to')) {
            return;
        }

        $this->dropForeignIfExists('client_issue_team_assignments', 'assigned_to');

        DB::statement('ALTER TABLE client_issue_team_assignments MODIFY assigned_to BIGINT UNSIGNED NULL');

        Schema::table('client_issue_team_assignments', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function resolveUserId($legacyId, array $legacyToUserMap): ?int
    {
        $legacyId = (int) $legacyId;

        if ($legacyId <= 0) {
            return null;
        }

        if (isset($legacyToUserMap[$legacyId])) {
            return (int) $legacyToUserMap[$legacyId];
        }

        return $this->userExists($legacyId) ? $legacyId : null;
    }

    private function userExists(int $userId): bool
    {
        if (!Schema::hasTable('users')) {
            return false;
        }

        return DB::table('users')->where('id', $userId)->exists();
    }

    private function dropForeignIfExists(string $table, string $column): void
    {
        $database = DB::getDatabaseName();

        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if (!$constraint) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
    }
};
