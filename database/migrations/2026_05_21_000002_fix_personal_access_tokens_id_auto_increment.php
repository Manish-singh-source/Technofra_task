<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $databaseName = DB::getDatabaseName();

        $hasPrimaryKey = DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', 'personal_access_tokens')
            ->where('index_name', 'PRIMARY')
            ->exists();

        if (! $hasPrimaryKey) {
            DB::statement('ALTER TABLE personal_access_tokens ADD PRIMARY KEY (id)');
        }

        DB::statement('ALTER TABLE personal_access_tokens MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE personal_access_tokens MODIFY id BIGINT UNSIGNED NOT NULL');
    }
};

