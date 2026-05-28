<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['project_id', 'status', 'priority'], 'tasks_project_status_priority_idx');
                $table->index(['project_id', 'workflow_status'], 'tasks_project_workflow_status_idx');
            });
        }

        if (Schema::hasTable('project_activities')) {
            Schema::table('project_activities', function (Blueprint $table) {
                $table->index(['project_id', 'activity_at'], 'project_activities_project_activity_at_idx');
            });
        }

        if (Schema::hasTable('project_milestones')) {
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->index(['project_id', 'status'], 'project_milestones_project_status_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('tasks_project_status_priority_idx');
                $table->dropIndex('tasks_project_workflow_status_idx');
            });
        }

        if (Schema::hasTable('project_activities')) {
            Schema::table('project_activities', function (Blueprint $table) {
                $table->dropIndex('project_activities_project_activity_at_idx');
            });
        }

        if (Schema::hasTable('project_milestones')) {
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->dropIndex('project_milestones_project_status_idx');
            });
        }
    }

};
