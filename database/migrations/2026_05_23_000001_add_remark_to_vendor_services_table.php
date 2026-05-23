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
        Schema::table('vendor_services', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_services', 'remark_text')) {
                $table->string('remark_text', 100)->nullable()->after('service_details');
            }

            if (! Schema::hasColumn('vendor_services', 'remark_color')) {
                $table->string('remark_color', 20)->nullable()->after('remark_text');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_services', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_services', 'remark_color')) {
                $table->dropColumn('remark_color');
            }

            if (Schema::hasColumn('vendor_services', 'remark_text')) {
                $table->dropColumn('remark_text');
            }
        });
    }
};
