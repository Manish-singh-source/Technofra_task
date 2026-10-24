<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('social_post_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reminder_type');
            $table->date('reminder_date');
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->unique(['social_post_id', 'user_id', 'reminder_type', 'reminder_date'], 'social_reminder_dedupe');
        });
    }
    public function down(): void { Schema::dropIfExists('social_post_reminders'); }
};
