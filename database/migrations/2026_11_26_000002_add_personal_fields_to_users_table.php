<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 40)->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'blood_group')) {
                $table->string('blood_group', 16)->nullable()->after('marital_status');
            }
            if (! Schema::hasColumn('users', 'guardian_name')) {
                $table->string('guardian_name', 150)->nullable()->after('blood_group');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship', 80)->nullable()->after('emergency_contact_phone');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_address')) {
                $table->text('emergency_contact_address')->nullable()->after('emergency_contact_relationship');
            }
            if (! Schema::hasColumn('users', 'current_address')) {
                $table->text('current_address')->nullable()->after('emergency_contact_address');
            }
            if (! Schema::hasColumn('users', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('current_address');
            }
        });
    }

    public function down(): void
    {
        $columns = [
            'marital_status',
            'blood_group',
            'guardian_name',
            'emergency_contact_relationship',
            'emergency_contact_address',
            'current_address',
            'permanent_address',
        ];

        $existing = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn('users', $col)));

        if (! empty($existing)) {
            Schema::table('users', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
