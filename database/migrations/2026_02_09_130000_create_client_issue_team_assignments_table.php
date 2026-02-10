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
        Schema::create('client_issue_team_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_issue_id')->constrained('client_issues')->onDelete('cascade');
            $table->string('team_name'); // Support Team, Graphic Team, Digital Marketing Team, Account Team
            $table->text('note')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_issue_team_assignments');
    }
};
