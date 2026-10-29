<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $database = DB::getDatabaseName();

        // Set the database default so new tables don't inherit uca1400
        DB::statement("ALTER DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Find every table not already on utf8mb4_unicode_ci (skip utf8mb4_bin — intentional)
        $tables = DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_COLLATION <> 'utf8mb4_unicode_ci'
              AND TABLE_COLLATION <> 'utf8mb4_bin'
        ");

        foreach ($tables as $row) {
            $table = $row->TABLE_NAME;
            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down(): void
    {
        // Intentionally left empty — collation changes are not reversed
    }
};
