<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order_reminders', 'sent_slot')) {
            Schema::table('order_reminders', function (Blueprint $table) {
                $table->string('sent_slot', 20)->default('morning')->after('sent_on');
            });
        }

        Schema::table('order_reminders', function (Blueprint $table) {
            $table->index('order_id', 'order_reminders_order_id_idx');
            $table->index('user_id', 'order_reminders_user_id_idx');
            $table->dropUnique('order_reminder_dedupe');
            $table->unique(['order_id', 'reminder_type', 'user_id', 'sent_on', 'sent_slot'], 'order_reminder_dedupe');
        });
    }

    public function down(): void
    {
        Schema::table('order_reminders', function (Blueprint $table) {
            $table->dropUnique('order_reminder_dedupe');
            $table->unique(['order_id', 'reminder_type', 'user_id', 'sent_on'], 'order_reminder_dedupe');
            $table->dropColumn('sent_slot');
        });
    }
};
