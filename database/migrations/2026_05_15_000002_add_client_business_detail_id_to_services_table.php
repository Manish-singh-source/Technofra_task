<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('client_business_detail_id')
                ->nullable()
                ->after('client_id')
                ->constrained('client_business_details')
                ->nullOnDelete();
        });

        DB::statement('
            UPDATE services
            LEFT JOIN (
                SELECT user_id, MIN(id) AS business_detail_id
                FROM client_business_details
                WHERE deleted_at IS NULL
                GROUP BY user_id
            ) AS primary_company ON primary_company.user_id = services.client_id
            SET services.client_business_detail_id = primary_company.business_detail_id
            WHERE services.client_business_detail_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_business_detail_id');
        });
    }
};
