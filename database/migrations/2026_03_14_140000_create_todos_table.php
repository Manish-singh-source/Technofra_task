<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('task_date');
            $table->time('task_time')->nullable();
            $table->unsignedInteger('repeat_interval')->default(1);
            $table->enum('repeat_unit', ['day', 'week', 'month', 'year'])->default('day');
            $table->json('repeat_days')->nullable();
            $table->time('reminder_time')->nullable();
            $table->date('starts_on');
            $table->enum('ends_type', ['never', 'on', 'after'])->default('never');
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('ends_after_occurrences')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->date('last_reminded_occurrence_on')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('todos');
    }
};
