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
        Schema::table('client_issue_tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('assigned_to');
            $table->time('due_time')->nullable()->after('due_date');
            $table->date('reminder_date')->nullable()->after('due_time');
            $table->time('reminder_time')->nullable()->after('reminder_date');
            $table->json('checklist_data')->nullable()->after('reminder_time');
            $table->json('labels_data')->nullable()->after('checklist_data');
            $table->string('attachment')->nullable()->after('labels_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_issue_tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'due_time', 'reminder_date', 'reminder_time', 'checklist_data', 'labels_data', 'attachment']);
        });
    }
};
