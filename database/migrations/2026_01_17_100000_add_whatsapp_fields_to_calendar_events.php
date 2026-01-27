<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('whatsapp_recipients')->nullable()->after('email_recipients');
            $table->boolean('reminder_10min_sent')->default(false)->after('notification_sent');
            $table->dateTime('reminder_10min_sent_at')->nullable()->after('reminder_10min_sent');
            $table->boolean('event_time_notification_sent')->default(false)->after('reminder_10min_sent_at');
            $table->dateTime('event_time_notification_sent_at')->nullable()->after('event_time_notification_sent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_recipients',
                'reminder_10min_sent',
                'reminder_10min_sent_at',
                'event_time_notification_sent',
                'event_time_notification_sent_at'
            ]);
        });
    }
};

