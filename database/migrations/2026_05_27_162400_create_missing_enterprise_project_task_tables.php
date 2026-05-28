<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_checklists')) {
            Schema::create('task_checklists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('task_checklists')->nullOnDelete();
                $table->string('title');
                $table->boolean('is_completed')->default(false);
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['task_id', 'is_completed']);
            });
        }

        if (! Schema::hasTable('task_time_logs')) {
            Schema::create('task_time_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->decimal('duration_minutes', 10, 2)->nullable();
                $table->text('note')->nullable();
                $table->enum('log_type', ['timer', 'manual'])->default('manual');
                $table->timestamps();

                $table->index(['task_id', 'user_id']);
                $table->index(['started_at', 'ended_at']);
            });
        }

        if (! Schema::hasTable('task_dependencies')) {
            Schema::create('task_dependencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
                $table->enum('dependency_type', ['blocks', 'depends_on', 'related_to'])->default('depends_on');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['task_id', 'depends_on_task_id', 'dependency_type'], 'task_dependency_unique');
                $table->index(['task_id', 'depends_on_task_id']);
            });
        }

        if (! Schema::hasTable('task_followers')) {
            Schema::create('task_followers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['task_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('project_activities')) {
            Schema::create('project_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('activity_type');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('activity_at')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'activity_at']);
                $table->index('activity_type');
            });
        }

        if (! Schema::hasTable('project_status_histories')) {
            Schema::create('project_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamp('changed_at')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'changed_at']);
            });
        }

        if (! Schema::hasTable('task_status_histories')) {
            Schema::create('task_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamp('changed_at')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'changed_at']);
            });
        }

        if (! Schema::hasTable('project_change_requests')) {
            Schema::create('project_change_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('impact_level')->nullable();
                $table->string('status')->default('requested');
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'status']);
            });
        }

        if (! Schema::hasTable('project_deployments')) {
            Schema::create('project_deployments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('deployed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('environment')->nullable();
                $table->string('version')->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('scheduled');
                $table->timestamp('deployed_at')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'deployed_at']);
            });
        }

        if (! Schema::hasTable('sprints')) {
            Schema::create('sprints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('name');
                $table->string('status')->default('planned');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedTinyInteger('velocity')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'status']);
            });
        }

        if (! Schema::hasTable('project_tags')) {
            Schema::create('project_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->string('name');
                $table->string('color')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'name']);
            });
        }

        if (! Schema::hasTable('task_tags')) {
            Schema::create('task_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->string('name');
                $table->string('color')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'name']);
            });
        }

        if (! Schema::hasTable('task_labels')) {
            Schema::create('task_labels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->string('label');
                $table->string('color')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'label']);
            });
        }

        if (! Schema::hasTable('project_approvals')) {
            Schema::create('project_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approval_type')->nullable();
                $table->string('status')->default('pending');
                $table->text('notes')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['project_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_approvals');
        Schema::dropIfExists('task_labels');
        Schema::dropIfExists('task_tags');
        Schema::dropIfExists('project_tags');
        Schema::dropIfExists('sprints');
        Schema::dropIfExists('project_deployments');
        Schema::dropIfExists('project_change_requests');
        Schema::dropIfExists('task_status_histories');
        Schema::dropIfExists('project_status_histories');
        Schema::dropIfExists('project_activities');
        Schema::dropIfExists('task_followers');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('task_time_logs');
        Schema::dropIfExists('task_checklists');
    }
};
