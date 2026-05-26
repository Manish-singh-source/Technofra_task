<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'status')) {
                $table->string('status')->nullable()->after('description');
            }
            if (! Schema::hasColumn('leads', 'previous_status')) {
                $table->string('previous_status')->nullable()->after('status');
            }
            if (! Schema::hasColumn('leads', 'status_updated_at')) {
                $table->dateTime('status_updated_at')->nullable()->after('previous_status');
            }
            if (! Schema::hasColumn('leads', 'status_updated_by')) {
                $table->unsignedBigInteger('status_updated_by')->nullable()->after('status_updated_at');
            }
            if (! Schema::hasColumn('leads', 'converted_at')) {
                $table->dateTime('converted_at')->nullable()->after('status_updated_by');
            }
            if (! Schema::hasColumn('leads', 'lost_at')) {
                $table->dateTime('lost_at')->nullable()->after('converted_at');
            }
            if (! Schema::hasColumn('leads', 'lost_reason')) {
                $table->text('lost_reason')->nullable()->after('lost_at');
            }
            if (! Schema::hasColumn('leads', 'won_value')) {
                $table->decimal('won_value', 12, 2)->nullable()->after('lost_reason');
            }
            if (! Schema::hasColumn('leads', 'pipeline_stage_order')) {
                $table->unsignedInteger('pipeline_stage_order')->nullable()->after('won_value');
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('status', 'leads_status_pipeline_idx');
            $table->index('assigned_to', 'leads_assigned_to_pipeline_idx');
            $table->index('status_updated_at', 'leads_status_updated_at_idx');
            $table->index('converted_at', 'leads_converted_at_idx');
            $table->index('lost_at', 'leads_lost_at_idx');
        });
    }

    public function down(): void
    {
        // forward-safe migration
    }
};
