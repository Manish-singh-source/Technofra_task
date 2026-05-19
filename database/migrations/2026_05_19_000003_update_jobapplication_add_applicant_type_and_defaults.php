<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jobapplication')) {
            return;
        }

        if (! Schema::hasColumn('jobapplication', 'applicant_type')) {
            DB::statement("ALTER TABLE jobapplication ADD COLUMN applicant_type VARCHAR(30) NOT NULL DEFAULT '' AFTER role");
        }

        DB::statement("ALTER TABLE jobapplication MODIFY applicant_type VARCHAR(30) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE jobapplication MODIFY experience VARCHAR(100) NOT NULL DEFAULT 'N/A'");
        DB::statement("ALTER TABLE jobapplication MODIFY ctc VARCHAR(100) NOT NULL DEFAULT 'N/A'");
        DB::statement("ALTER TABLE jobapplication MODIFY ectc VARCHAR(100) NOT NULL DEFAULT 'N/A'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('jobapplication')) {
            return;
        }

        DB::statement("ALTER TABLE jobapplication MODIFY experience VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE jobapplication MODIFY ctc VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE jobapplication MODIFY ectc VARCHAR(100) NOT NULL");

        if (Schema::hasColumn('jobapplication', 'applicant_type')) {
            DB::statement("ALTER TABLE jobapplication DROP COLUMN applicant_type");
        }
    }
};

