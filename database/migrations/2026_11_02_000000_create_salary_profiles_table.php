<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_code')->nullable()->unique();
            $table->string('salarybox_id')->nullable()->unique();
            $table->decimal('monthly_salary', 12, 2)->default(0);
            $table->decimal('standard_deduction', 12, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_last4', 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_profiles');
    }
};
