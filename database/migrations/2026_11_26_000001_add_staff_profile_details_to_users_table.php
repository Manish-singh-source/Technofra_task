<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 32)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'alternate_phone')) {
                $table->string('alternate_phone', 25)->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name', 120)->nullable()->after('alternate_phone');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 25)->nullable()->after('emergency_contact_name');
            }
            if (! Schema::hasColumn('users', 'branch')) {
                $table->string('branch', 150)->nullable()->default('Technofra Main Branch')->after('emergency_contact_phone');
            }
            if (! Schema::hasColumn('users', 'employee_code')) {
                $table->string('employee_code', 50)->nullable()->after('branch');
            }
            if (! Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 150)->nullable()->after('employee_code');
            }
            if (! Schema::hasColumn('users', 'employee_type')) {
                $table->string('employee_type', 50)->nullable()->after('designation');
            }
            if (! Schema::hasColumn('users', 'date_of_joining')) {
                $table->date('date_of_joining')->nullable()->after('employee_type');
            }
            if (! Schema::hasColumn('users', 'date_of_leaving')) {
                $table->date('date_of_leaving')->nullable()->after('date_of_joining');
            }
            if (! Schema::hasColumn('users', 'work_mode')) {
                $table->string('work_mode', 50)->nullable()->after('date_of_leaving');
            }
            if (! Schema::hasColumn('users', 'reporting_manager')) {
                $table->string('reporting_manager', 150)->nullable()->after('work_mode');
            }
        });
    }

    public function down(): void
    {
        $columns = [
            'gender',
            'date_of_birth',
            'alternate_phone',
            'emergency_contact_name',
            'emergency_contact_phone',
            'branch',
            'employee_code',
            'designation',
            'employee_type',
            'date_of_joining',
            'date_of_leaving',
            'work_mode',
            'reporting_manager',
        ];

        $existing = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn('users', $col)));

        if (! empty($existing)) {
            Schema::table('users', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
