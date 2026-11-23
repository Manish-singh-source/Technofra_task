<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_content_plans', function (Blueprint $table) {
            $table->date('plan_date')->nullable()->after('client_id');
        });

        DB::table('social_content_plans')->update([
            'plan_date' => DB::raw("STR_TO_DATE(CONCAT(plan_year, '-', LPAD(plan_month, 2, '0'), '-01'), '%Y-%m-%d')"),
        ]);
    }

    public function down(): void
    {
        Schema::table('social_content_plans', function (Blueprint $table) {
            $table->dropColumn('plan_date');
        });
    }
};
