<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $statusValues = ['new', 'contacted', 'qualified', 'converted', 'loss'];

        if (Schema::hasTable('leads') && ! Schema::hasColumn('leads', 'status')) {
            Schema::table('leads', function (Blueprint $table) use ($statusValues) {
                $table->enum('status', $statusValues)->default('new')->after('description');
            });
        }

        if (Schema::hasTable('digital_marketing_leads') && ! Schema::hasColumn('digital_marketing_leads', 'status')) {
            Schema::table('digital_marketing_leads', function (Blueprint $table) use ($statusValues) {
                $table->enum('status', $statusValues)->default('new')->after('source_page');
            });
        }

        if (Schema::hasTable('webapp_leads') && ! Schema::hasColumn('webapp_leads', 'status')) {
            Schema::table('webapp_leads', function (Blueprint $table) use ($statusValues) {
                $table->enum('status', $statusValues)->default('new')->after('source_page');
            });
        }

        if (Schema::hasTable('meta_leads') && ! Schema::hasColumn('meta_leads', 'status')) {
            Schema::table('meta_leads', function (Blueprint $table) use ($statusValues) {
                $table->enum('status', $statusValues)->default('new')->after('field_data');
            });
        }

        if (Schema::hasTable('google_leads') && ! Schema::hasColumn('google_leads', 'status')) {
            Schema::table('google_leads', function (Blueprint $table) use ($statusValues) {
                $table->enum('status', $statusValues)->default('new')->after('lead_stage');
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. We only add missing columns in `up()`
        // and cannot safely determine which tables had pre-existing status fields.
    }
};
