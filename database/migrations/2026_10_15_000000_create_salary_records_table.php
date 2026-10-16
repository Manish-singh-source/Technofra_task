<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('salary_month');
            $table->json('data');
            $table->timestamps();
            $table->unique(['staff_user_id', 'salary_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
