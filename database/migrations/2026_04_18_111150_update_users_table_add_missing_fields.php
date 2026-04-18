<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            // $table->dropColumn('status');
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('profile_image')->nullable()->after('password');
            $table->string('status')->default('active')->after('profile_image');
            $table->string('role')->nullable()->after('status');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['first_name', 'last_name', 'phone', 'profile_image', 'status', 'role']);
            $table->string('name')->after('id');
            $table->string('status')->default('active')->after('email_verified_at');
        });
    }
};
