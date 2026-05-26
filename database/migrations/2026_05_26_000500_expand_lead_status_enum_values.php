<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = "'new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk', 'converted'";

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'status')) {
            DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM({$statuses}) NOT NULL DEFAULT 'new'");
        }

        if (Schema::hasTable('digital_marketing_leads') && Schema::hasColumn('digital_marketing_leads', 'status')) {
            DB::statement("ALTER TABLE `digital_marketing_leads` MODIFY `status` ENUM({$statuses}) NOT NULL DEFAULT 'new'");
        }

        if (Schema::hasTable('webapp_leads') && Schema::hasColumn('webapp_leads', 'status')) {
            DB::statement("ALTER TABLE `webapp_leads` MODIFY `status` ENUM({$statuses}) NOT NULL DEFAULT 'new'");
        }

        if (Schema::hasTable('meta_leads') && Schema::hasColumn('meta_leads', 'status')) {
            DB::statement("ALTER TABLE `meta_leads` MODIFY `status` ENUM({$statuses}) NOT NULL DEFAULT 'new'");
        }

        if (Schema::hasTable('google_leads') && Schema::hasColumn('google_leads', 'status')) {
            DB::statement("ALTER TABLE `google_leads` MODIFY `status` ENUM({$statuses}) NOT NULL DEFAULT 'new'");
        }
    }

    public function down(): void
    {
        // Keep forward-compatible status values.
    }
};
