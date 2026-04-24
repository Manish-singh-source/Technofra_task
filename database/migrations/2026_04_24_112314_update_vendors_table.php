<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('new_status', ['active', 'inactive'])
                  ->default('active')
                  ->after('address');
        });

        DB::table('vendors')
            ->where('status', 1)
            ->update(['new_status' => 'active']);

        DB::table('vendors')
            ->where('status', 0)
            ->update(['new_status' => 'inactive']);

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('new_status', 'status');
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('old_status')
                  ->default(1)
                  ->after('address');
        });

        DB::table('vendors')
            ->where('status', 'active')
            ->update(['old_status' => 1]);

        DB::table('vendors')
            ->where('status', 'inactive')
            ->update(['old_status' => 0]);

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('old_status', 'status');
        });
    }
};