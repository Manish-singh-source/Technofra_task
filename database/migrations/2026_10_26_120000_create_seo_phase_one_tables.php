<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('website_url', 2048);
            $table->string('industry')->nullable();
            $table->string('target_country', 100)->nullable();
            $table->json('target_locations')->nullable();
            $table->string('target_language', 20)->default('en');
            $table->string('search_engine', 50)->default('google');
            $table->json('devices')->nullable();
            $table->date('start_date');
            $table->text('goals')->nullable();
            $table->string('reporting_frequency', 30)->default('monthly');
            $table->enum('status', ['draft', 'active', 'on_hold', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['client_id', 'status']);
        });

        Schema::create('seo_campaign_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_campaign_id')->constrained('seo_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('campaign_role', ['manager', 'executive']);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();
            $table->unique(['seo_campaign_id', 'user_id', 'campaign_role'], 'seo_campaign_member_unique');
        });

        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_campaign_id')->constrained('seo_campaigns')->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'changes_required', 'approved'])->default('draft');
            $table->text('summary')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_feedback')->nullable();
            $table->timestamps();
            $table->index(['seo_campaign_id', 'status']);
        });

        Schema::create('seo_audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_audit_id')->constrained('seo_audits')->cascadeOnDelete();
            $table->enum('category', ['technical', 'on_page', 'performance', 'other']);
            $table->string('title');
            $table->string('affected_url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->text('recommended_solution')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'verified', 'closed'])->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['seo_audit_id', 'category', 'status']);
        });

        Schema::create('seo_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_campaign_id')->constrained('seo_campaigns')->cascadeOnDelete();
            $table->foreignId('seo_audit_id')->nullable()->constrained('seo_audits')->nullOnDelete();
            $table->date('captured_on');
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('seo_campaign_id');
        });

        Schema::create('seo_baseline_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_baseline_id')->constrained('seo_baselines')->cascadeOnDelete();
            $table->string('metric_key', 100);
            $table->decimal('metric_value', 18, 4)->nullable();
            $table->string('unit', 30)->nullable();
            $table->string('source', 100)->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();
            $table->unique(['seo_baseline_id', 'metric_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_baseline_metrics');
        Schema::dropIfExists('seo_baselines');
        Schema::dropIfExists('seo_audit_findings');
        Schema::dropIfExists('seo_audits');
        Schema::dropIfExists('seo_campaign_members');
        Schema::dropIfExists('seo_campaigns');
    }
};
