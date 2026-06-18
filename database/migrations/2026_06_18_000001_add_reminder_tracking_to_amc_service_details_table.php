<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('amc_service_details', function (Blueprint $table) {
            $table->timestamp('before_visit_reminder_sent_at')->nullable()->after('completed_at');
            $table->timestamp('same_day_reminder_sent_at')->nullable()->after('before_visit_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amc_service_details', function (Blueprint $table) {
            $table->dropColumn(['before_visit_reminder_sent_at', 'same_day_reminder_sent_at']);
        });
    }
};
