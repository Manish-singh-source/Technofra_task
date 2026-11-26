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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'aadhar_number')) {
                $table->string('aadhar_number', 32)->nullable()->after('official_email');
            }
            if (! Schema::hasColumn('users', 'aadhar_front_image')) {
                $table->string('aadhar_front_image', 255)->nullable()->after('aadhar_number');
            }
            if (! Schema::hasColumn('users', 'aadhar_back_image')) {
                $table->string('aadhar_back_image', 255)->nullable()->after('aadhar_front_image');
            }
            if (! Schema::hasColumn('users', 'pan_number')) {
                $table->string('pan_number', 32)->nullable()->after('aadhar_back_image');
            }
            if (! Schema::hasColumn('users', 'pan_front_image')) {
                $table->string('pan_front_image', 255)->nullable()->after('pan_number');
            }
            if (! Schema::hasColumn('users', 'account_number')) {
                $table->string('account_number', 64)->nullable()->after('pan_front_image');
            }
            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name', 150)->nullable()->after('account_number');
            }
            if (! Schema::hasColumn('users', 'ifsc_code')) {
                $table->string('ifsc_code', 32)->nullable()->after('bank_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'aadhar_number',
                'aadhar_front_image',
                'aadhar_back_image',
                'pan_number',
                'pan_front_image',
                'account_number',
                'bank_name',
                'ifsc_code',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
