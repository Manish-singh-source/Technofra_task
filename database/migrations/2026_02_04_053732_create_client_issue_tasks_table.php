<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_issue_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_issue_id')->constrained('client_issues')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo');
            $table->string('priority')->default('medium');
            $table->string('assigned_to')->nullable();
             $table->date('start_date')->nullable();
            $table->time('due_time')->nullable();
            $table->date('reminder_date')->nullable();
            $table->time('reminder_time')->nullable();
            $table->json('checklist_data')->nullable();
            $table->json('labels_data')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_issue_tasks');
    }
};
