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
        Schema::table('client_issue_team_assignments', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('team_name')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_issue_team_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};
