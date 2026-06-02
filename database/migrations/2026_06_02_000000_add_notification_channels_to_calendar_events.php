<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->json('notification_channels')->nullable()->after('whatsapp_recipients');
        });
    }

    public function down()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('notification_channels');
        });
    }
};
