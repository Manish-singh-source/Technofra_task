<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_followups')) {
            return;
        }

        Schema::table('lead_followups', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_followups', 'source_type')) {
                $table->string('source_type', 50)->nullable()->after('lead_id');
            }
            if (! Schema::hasColumn('lead_followups', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }

            $table->index(['source_type', 'source_id'], 'lead_followups_source_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lead_followups')) {
            return;
        }

        Schema::table('lead_followups', function (Blueprint $table) {
            if (Schema::hasColumn('lead_followups', 'source_type') || Schema::hasColumn('lead_followups', 'source_id')) {
                $table->dropIndex('lead_followups_source_idx');
            }
            if (Schema::hasColumn('lead_followups', 'source_id')) {
                $table->dropColumn('source_id');
            }
            if (Schema::hasColumn('lead_followups', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};

