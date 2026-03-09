<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tasks') || !Schema::hasColumn('tasks', 'project_id')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE tasks DROP FOREIGN KEY tasks_project_id_foreign');
        DB::statement('ALTER TABLE tasks MODIFY project_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE tasks ADD CONSTRAINT tasks_project_id_foreign FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tasks') || !Schema::hasColumn('tasks', 'project_id')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $fallbackProjectId = DB::table('projects')->orderBy('id')->value('id');

        if (!$fallbackProjectId) {
            throw new RuntimeException('Cannot revert tasks.project_id to NOT NULL because projects table is empty.');
        }

        DB::table('tasks')->whereNull('project_id')->update(['project_id' => $fallbackProjectId]);

        DB::statement('ALTER TABLE tasks DROP FOREIGN KEY tasks_project_id_foreign');
        DB::statement('ALTER TABLE tasks MODIFY project_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE tasks ADD CONSTRAINT tasks_project_id_foreign FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE');
    }
};
