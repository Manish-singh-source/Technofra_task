<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'task_code')) {
                $table->string('task_code')->nullable()->after('title');
                $table->index('task_code');
            }

            if (! Schema::hasColumn('tasks', 'milestone_id')) {
                $table->foreignId('milestone_id')->nullable()->after('project_id')->constrained('project_milestones')->nullOnDelete();
                $table->index('milestone_id');
            }

            if (! Schema::hasColumn('tasks', 'parent_task_id')) {
                $table->foreignId('parent_task_id')->nullable()->after('milestone_id')->constrained('tasks')->nullOnDelete();
                $table->index('parent_task_id');
            }

            if (! Schema::hasColumn('tasks', 'task_type')) {
                $table->string('task_type')->nullable()->after('priority');
                $table->index('task_type');
            }

            if (! Schema::hasColumn('tasks', 'estimated_hours')) {
                $table->decimal('estimated_hours', 10, 2)->nullable()->after('task_type');
            }

            if (! Schema::hasColumn('tasks', 'actual_hours')) {
                $table->decimal('actual_hours', 10, 2)->nullable()->after('estimated_hours');
            }

            if (! Schema::hasColumn('tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('actual_hours');
            }

            if (! Schema::hasColumn('tasks', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
                $table->index('reviewed_by');
            }

            if (! Schema::hasColumn('tasks', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('tasks', 'qa_status')) {
                $table->string('qa_status')->nullable()->after('reviewed_at');
                $table->index('qa_status');
            }

            if (! Schema::hasColumn('tasks', 'blocked_reason')) {
                $table->text('blocked_reason')->nullable()->after('qa_status');
            }

            if (! Schema::hasColumn('tasks', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('blocked_reason');
            }

            if (! Schema::hasColumn('tasks', 'deployed_at')) {
                $table->timestamp('deployed_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('tasks', 'sequence_order')) {
                $table->unsignedInteger('sequence_order')->nullable()->after('deployed_at');
                $table->index('sequence_order');
            }

            if (! Schema::hasColumn('tasks', 'sprint_id')) {
                $table->unsignedBigInteger('sprint_id')->nullable()->after('sequence_order');
                $table->index('sprint_id');
            }

            if (! Schema::hasColumn('tasks', 'severity')) {
                $table->string('severity')->nullable()->after('sprint_id');
                $table->index('severity');
            }

            if (! Schema::hasColumn('tasks', 'story_points')) {
                $table->unsignedTinyInteger('story_points')->nullable()->after('severity');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'story_points')) {
                $table->dropColumn('story_points');
            }

            if (Schema::hasColumn('tasks', 'severity')) {
                $table->dropIndex(['severity']);
                $table->dropColumn('severity');
            }

            if (Schema::hasColumn('tasks', 'sprint_id')) {
                $table->dropIndex(['sprint_id']);
                $table->dropColumn('sprint_id');
            }

            if (Schema::hasColumn('tasks', 'sequence_order')) {
                $table->dropIndex(['sequence_order']);
                $table->dropColumn('sequence_order');
            }

            if (Schema::hasColumn('tasks', 'deployed_at')) {
                $table->dropColumn('deployed_at');
            }

            if (Schema::hasColumn('tasks', 'started_at')) {
                $table->dropColumn('started_at');
            }

            if (Schema::hasColumn('tasks', 'blocked_reason')) {
                $table->dropColumn('blocked_reason');
            }

            if (Schema::hasColumn('tasks', 'qa_status')) {
                $table->dropIndex(['qa_status']);
                $table->dropColumn('qa_status');
            }

            if (Schema::hasColumn('tasks', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('tasks', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }

            if (Schema::hasColumn('tasks', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

            if (Schema::hasColumn('tasks', 'actual_hours')) {
                $table->dropColumn('actual_hours');
            }

            if (Schema::hasColumn('tasks', 'estimated_hours')) {
                $table->dropColumn('estimated_hours');
            }

            if (Schema::hasColumn('tasks', 'task_type')) {
                $table->dropIndex(['task_type']);
                $table->dropColumn('task_type');
            }

            if (Schema::hasColumn('tasks', 'parent_task_id')) {
                $table->dropConstrainedForeignId('parent_task_id');
            }

            if (Schema::hasColumn('tasks', 'milestone_id')) {
                $table->dropConstrainedForeignId('milestone_id');
            }

            if (Schema::hasColumn('tasks', 'task_code')) {
                $table->dropIndex(['task_code']);
                $table->dropColumn('task_code');
            }
        });
    }
};
