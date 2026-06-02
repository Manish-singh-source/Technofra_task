<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->json('reminder_delivery_log')->nullable()->after('notification_channels');
        });

        Schema::table('todos', function (Blueprint $table) {
            $table->json('reminder_delivery_log')->nullable()->after('last_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('reminder_delivery_log');
        });

        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn('reminder_delivery_log');
        });
    }
};
