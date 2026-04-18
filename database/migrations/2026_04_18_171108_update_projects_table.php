<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'customer_id')) {
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Throwable $e) {
                    // Ignore when the constraint is already missing.
                }
            }
        });

        if (Schema::hasColumn('projects', 'customer_id')) {
            DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NULL');
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'customer_id')) {
                $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'customer_id')) {
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Throwable $e) {
                    // Ignore when the constraint is already missing.
                }
            }
        });

        if (Schema::hasColumn('projects', 'customer_id')) {
            DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'customer_id')) {
                $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }
};
