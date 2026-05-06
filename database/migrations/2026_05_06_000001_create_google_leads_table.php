<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('google_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_id')->unique();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->bigInteger('form_id')->nullable();
            $table->bigInteger('campaign_id')->nullable();
            $table->string('gcl_id')->nullable();
            $table->boolean('is_test')->default(false);
            $table->string('lead_stage')->nullable();
            $table->timestamp('lead_submit_time')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_leads');
    }
};
