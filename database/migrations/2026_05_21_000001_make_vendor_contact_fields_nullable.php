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
        DB::statement('ALTER TABLE vendors MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vendors MODIFY phone VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE vendors SET email = CONCAT('missing-email-', id, '@example.invalid') WHERE email IS NULL");
        DB::statement("UPDATE vendors SET phone = '' WHERE phone IS NULL");

        DB::statement('ALTER TABLE vendors MODIFY email VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vendors MODIFY phone VARCHAR(255) NOT NULL');
    }
};

