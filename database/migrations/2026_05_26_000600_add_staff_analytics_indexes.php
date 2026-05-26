<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            $this->addIndexIfMissing('leads', 'leads_assigned_to_idx2', ['assigned_to']);
            $this->addIndexIfMissing('leads', 'leads_status_idx2', ['status']);
            $this->addIndexIfMissing('leads', 'leads_next_followup_at_idx2', ['next_followup_at']);
            $this->addIndexIfMissing('leads', 'leads_created_at_idx2', ['created_at']);
            $this->addIndexIfMissing('leads', 'leads_converted_at_idx2', ['converted_at']);
        }

        if (Schema::hasTable('lead_followups')) {
            $this->addIndexIfMissing('lead_followups', 'lead_followups_followup_date_idx2', ['followup_date']);
            $this->addIndexIfMissing('lead_followups', 'lead_followups_staff_id_idx2', ['staff_id']);
            $this->addIndexIfMissing('lead_followups', 'lead_followups_created_at_idx2', ['created_at']);
        }

        if (Schema::hasTable('lead_activities')) {
            $this->addIndexIfMissing('lead_activities', 'lead_activities_user_id_idx2', ['user_id']);
            $this->addIndexIfMissing('lead_activities', 'lead_activities_created_at_idx2', ['created_at']);
        }

        if (Schema::hasTable('lead_status_histories')) {
            $this->addIndexIfMissing('lead_status_histories', 'lead_status_histories_lead_id_idx2', ['lead_id']);
            $this->addIndexIfMissing('lead_status_histories', 'lead_status_histories_created_at_idx2', ['created_at']);
        }

        if (Schema::hasTable('lead_assignments')) {
            $this->addIndexIfMissing('lead_assignments', 'lead_assignments_assigned_to_idx2', ['assigned_to']);
            $this->addIndexIfMissing('lead_assignments', 'lead_assignments_created_at_idx2', ['created_at']);
        }
    }

    public function down(): void
    {
        // forward-safe
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::selectOne(
            'SELECT COUNT(1) as total FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$database, $table, $indexName]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
