<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NULL');

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $fallbackCustomerId = DB::table('users')->where('role', 'client')->orderBy('id')->value('id');

        if ($fallbackCustomerId !== null) {
            DB::table('projects')->whereNull('customer_id')->update(['customer_id' => $fallbackCustomerId]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NOT NULL');

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
