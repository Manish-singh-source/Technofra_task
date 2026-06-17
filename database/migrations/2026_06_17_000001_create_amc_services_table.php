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
        Schema::create('amc_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->unique()->constrained('services')->cascadeOnDelete();
            $table->unsignedInteger('total_visits');
            $table->date('amc_start_date');
            $table->date('amc_end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amc_services');
    }
};
