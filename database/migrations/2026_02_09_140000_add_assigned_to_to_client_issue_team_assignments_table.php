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
            $table->string('assigned_to')->nullable()->after('team_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_issue_team_assignments', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });
    }
};
