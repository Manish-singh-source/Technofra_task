<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_content_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('plan_month');
            $table->unsignedSmallInteger('plan_year');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending_client_review', 'client_approved', 'changes_requested'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['client_id', 'plan_month', 'plan_year'], 'social_plan_client_month_unique');
        });

        Schema::create('social_festival_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('event_date')->nullable();
            $table->string('category')->nullable();
            $table->string('country_region')->nullable();
            $table->enum('recurrence_type', ['none', 'yearly'])->default('yearly');
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('reference_url')->nullable();
            $table->timestamps();
        });

        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_content_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('content_category')->nullable();
            $table->enum('post_type', ['image', 'carousel', 'reel', 'video', 'story', 'other'])->default('image');
            $table->text('creative_brief')->nullable();
            $table->longText('caption')->nullable();
            $table->text('hashtags')->nullable();
            $table->text('reference_links')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamp('planned_at')->nullable();
            $table->timestamp('internal_deadline_at')->nullable();
            $table->timestamp('client_approval_deadline_at')->nullable();
            $table->timestamp('publishing_scheduled_at')->nullable();
            $table->enum('status', ['planned', 'assigned', 'in_progress', 'internal_review', 'internal_changes_requested', 'pending_client_approval', 'client_changes_requested', 'client_approved', 'ready_for_publishing', 'scheduled', 'published', 'completed', 'on_hold', 'rejected', 'cancelled'])->default('planned');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['client_id', 'status', 'planned_at']);
        });

        Schema::create('social_post_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('published_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['social_post_id', 'platform']);
        });

        Schema::create('social_post_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('assignment_role', ['designer', 'writer', 'reviewer', 'publisher']);
            $table->timestamps();
            $table->unique(['social_post_id', 'user_id', 'assignment_role'], 'social_post_assignment_unique');
        });

        Schema::create('social_post_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->longText('caption_snapshot')->nullable();
            $table->text('hashtags_snapshot')->nullable();
            $table->json('publishing_snapshot')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_for_review_at')->nullable();
            $table->timestamps();
            $table->unique(['social_post_id', 'version_number']);
        });

        Schema::create('social_post_version_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_version_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('social_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_post_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('social_post_comments')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
            $table->index(['social_post_id', 'is_internal']);
        });

        Schema::create('social_post_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_post_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('decided_by')->constrained('users')->cascadeOnDelete();
            $table->enum('stage', ['internal', 'client', 'plan']);
            $table->enum('decision', ['approved', 'changes_requested', 'rejected']);
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('social_post_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('action');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['social_post_id', 'created_at']);
        });

        Schema::create('social_post_publishing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['ready_to_schedule', 'scheduled', 'published', 'delayed', 'failed', 'cancelled'])->default('ready_to_schedule');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('actual_published_at')->nullable();
            $table->string('published_url')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_publishing_records');
        Schema::dropIfExists('social_post_status_histories');
        Schema::dropIfExists('social_post_approvals');
        Schema::dropIfExists('social_post_comments');
        Schema::dropIfExists('social_post_version_files');
        Schema::dropIfExists('social_post_versions');
        Schema::dropIfExists('social_post_assignments');
        Schema::dropIfExists('social_post_platforms');
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('social_festival_events');
        Schema::dropIfExists('social_content_plans');
    }
};
