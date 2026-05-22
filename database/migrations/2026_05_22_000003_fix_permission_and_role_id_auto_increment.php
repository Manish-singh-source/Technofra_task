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

        $hasPermissionsPrimaryKey = DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', 'permissions')
            ->where('index_name', 'PRIMARY')
            ->exists();

        if (! $hasPermissionsPrimaryKey) {
            DB::statement('ALTER TABLE permissions ADD PRIMARY KEY (id)');
        }

        DB::statement('ALTER TABLE permissions MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        $hasRolesPrimaryKey = DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', 'roles')
            ->where('index_name', 'PRIMARY')
            ->exists();

        if (! $hasRolesPrimaryKey) {
            DB::statement('ALTER TABLE roles ADD PRIMARY KEY (id)');
        }

        DB::statement('ALTER TABLE roles MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE permissions MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE roles MODIFY id BIGINT UNSIGNED NOT NULL');
    }
};

