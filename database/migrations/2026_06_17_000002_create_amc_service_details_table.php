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
        Schema::create('amc_service_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amc_service_id')->constrained('amc_services')->cascadeOnDelete();
            $table->unsignedInteger('visit_number');
            $table->date('visit_date');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->text('details')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amc_service_details');
    }
};
