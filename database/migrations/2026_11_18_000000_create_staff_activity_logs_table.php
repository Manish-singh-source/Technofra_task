<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('staff_name');
            $table->string('staff_email');
            $table->string('session_id', 100)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at')->index();
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'logged_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_activity_logs');
    }
};
