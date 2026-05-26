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
            if (! Schema::hasColumn('leads', 'lead_code')) {
                $table->string('lead_code')->nullable()->after('id');
            }
            if (! Schema::hasColumn('leads', 'company_name')) {
                $table->string('company_name')->nullable()->after('company');
            }
            if (! Schema::hasColumn('leads', 'source')) {
                $table->string('source')->nullable()->after('lead_value');
            }
            if (! Schema::hasColumn('leads', 'industry')) {
                $table->string('industry')->nullable()->after('source');
            }
            if (! Schema::hasColumn('leads', 'priority')) {
                $table->string('priority')->nullable()->after('industry');
            }
            if (! Schema::hasColumn('leads', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('leads', 'expected_value')) {
                $table->decimal('expected_value', 12, 2)->nullable()->after('assigned_to');
            }
            if (! Schema::hasColumn('leads', 'next_followup_at')) {
                $table->dateTime('next_followup_at')->nullable()->after('expected_value');
            }
            if (! Schema::hasColumn('leads', 'requirements')) {
                $table->longText('requirements')->nullable()->after('next_followup_at');
            }
            if (! Schema::hasColumn('leads', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('requirements');
            }
            if (! Schema::hasColumn('leads', 'converted_at')) {
                $table->dateTime('converted_at')->nullable()->after('created_by');
            }
            if (! Schema::hasColumn('leads', 'lost_reason')) {
                $table->text('lost_reason')->nullable()->after('converted_at');
            }
            if (! Schema::hasColumn('leads', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('assigned_to', 'leads_assigned_to_idx');
            $table->index('next_followup_at', 'leads_next_followup_at_idx');
            $table->index('status', 'leads_status_idx');
            $table->index('created_at', 'leads_created_at_idx');
        });
    }

    public function down(): void
    {
        // Safe forward-only migration for existing production data.
    }
};
