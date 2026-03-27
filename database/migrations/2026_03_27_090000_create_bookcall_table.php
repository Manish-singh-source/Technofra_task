<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookcall')) {
            return;
        }

        Schema::create('bookcall', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 25);
            $table->date('booking_date');
            $table->time('booking_time');
            $table->dateTime('booking_datetime');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookcall');
    }
};
