<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status_reason')) {
                $table->text('status_reason')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'status_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status_reason');
            });
        }
    }
};
