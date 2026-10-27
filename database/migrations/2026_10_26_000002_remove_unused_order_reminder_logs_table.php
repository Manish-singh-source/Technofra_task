<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('order_reminder_logs');
    }

    public function down(): void
    {
        // The reminder history is stored in order_reminders; no legacy table is restored.
    }
};
