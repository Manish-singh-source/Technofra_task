<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_assignments')) {
            Schema::create('lead_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->text('assignment_note')->nullable();
                $table->boolean('active')->default(true);
                $table->dateTime('assigned_at')->nullable();
                $table->timestamps();

                $table->index('lead_id');
                $table->index('assigned_to');
                $table->index(['lead_id', 'active']);
            });
        }

        if (! Schema::hasTable('lead_followups')) {
            Schema::create('lead_followups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->dateTime('followup_date');
                $table->string('followup_type')->nullable();
                $table->string('outcome')->nullable();
                $table->longText('discussion_notes')->nullable();
                $table->dateTime('next_followup_date')->nullable();
                $table->string('lead_status_after_followup')->nullable();
                $table->boolean('reminder_sent')->default(false);
                $table->timestamps();

                $table->index('lead_id');
                $table->index('staff_id');
                $table->index('followup_date');
                $table->index('next_followup_date');
            });
        }

        if (! Schema::hasTable('lead_activities')) {
            Schema::create('lead_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('activity_type');
                $table->text('description');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index('lead_id');
                $table->index('user_id');
                $table->index('activity_type');
            });
        }

        if (! Schema::hasTable('lead_notes')) {
            Schema::create('lead_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->longText('note');
                $table->boolean('is_private')->default(false);
                $table->timestamps();

                $table->index('lead_id');
                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('lead_reminders')) {
            Schema::create('lead_reminders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->dateTime('remind_at');
                $table->string('reminder_type')->default('dashboard');
                $table->string('status')->default('pending');
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();

                $table->index('lead_id');
                $table->index('user_id');
                $table->index('remind_at');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('lead_status_histories')) {
            Schema::create('lead_status_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->string('old_status')->nullable();
                $table->string('new_status');
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index('lead_id');
                $table->index('changed_by');
                $table->index('new_status');
            });
        }

        if (! Schema::hasTable('lead_escalations')) {
            Schema::create('lead_escalations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('escalated_from')->nullable();
                $table->unsignedBigInteger('escalated_to')->nullable();
                $table->text('reason')->nullable();
                $table->dateTime('escalated_at')->nullable();
                $table->timestamps();

                $table->index('lead_id');
                $table->index('escalated_to');
            });
        }

        if (! Schema::hasTable('lead_conversions')) {
            Schema::create('lead_conversions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('converted_by')->nullable();
                $table->decimal('conversion_value', 12, 2)->nullable();
                $table->dateTime('converted_at')->nullable();
                $table->timestamps();

                $table->index('lead_id');
                $table->index('converted_by');
                $table->index('converted_at');
            });
        }

        if (! Schema::hasTable('staff_lead_stats')) {
            Schema::create('staff_lead_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->unsignedInteger('total_leads')->default(0);
                $table->unsignedInteger('converted_leads')->default(0);
                $table->unsignedInteger('lost_leads')->default(0);
                $table->unsignedInteger('pending_followups')->default(0);
                $table->unsignedInteger('total_calls')->default(0);
                $table->unsignedInteger('total_meetings')->default(0);
                $table->decimal('conversion_rate', 5, 2)->default(0);
                $table->timestamps();

                $table->unique('staff_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_lead_stats');
        Schema::dropIfExists('lead_conversions');
        Schema::dropIfExists('lead_escalations');
        Schema::dropIfExists('lead_status_histories');
        Schema::dropIfExists('lead_reminders');
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('lead_followups');
        Schema::dropIfExists('lead_assignments');
    }
};
