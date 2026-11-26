<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'official_email')) {
                $table->string('official_email', 255)->nullable()->after('reporting_manager');
            }
            if (! Schema::hasColumn('users', 'esi_number')) {
                $table->string('esi_number', 64)->nullable()->after('official_email');
            }
            if (! Schema::hasColumn('users', 'pf_number')) {
                $table->string('pf_number', 64)->nullable()->after('esi_number');
            }
            if (! Schema::hasColumn('users', 'past_employment')) {
                $table->json('past_employment')->nullable()->after('pf_number');
            }
        });
    }

    public function down(): void
    {
        $columns = [
            'official_email',
            'esi_number',
            'pf_number',
            'past_employment',
        ];

        $existing = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn('users', $col)));

        if (! empty($existing)) {
            Schema::table('users', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
