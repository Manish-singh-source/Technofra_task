<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->boolean('reminder_email')->default(true)->after('reminder_time');
            $table->boolean('reminder_whatsapp')->default(false)->after('reminder_email');
        });
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn(['reminder_email', 'reminder_whatsapp']);
        });
    }
};

