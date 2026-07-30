<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('enquiry_forms') || Schema::hasColumn('enquiry_forms', 'status')) {
            return;
        }

        Schema::table('enquiry_forms', function (Blueprint $table) {
            $table->string('status', 50)->default('new')->after('source_page');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('enquiry_forms') || ! Schema::hasColumn('enquiry_forms', 'status')) {
            return;
        }

        Schema::table('enquiry_forms', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};