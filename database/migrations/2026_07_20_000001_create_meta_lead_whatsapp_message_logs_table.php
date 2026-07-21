<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_lead_whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_lead_id')->constrained('meta_leads')->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('template_name')->nullable();
            $table->json('template_parameters')->nullable();
            $table->string('status', 20)->default('queued');
            $table->unsignedSmallInteger('provider_status_code')->nullable();
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['meta_lead_id', 'status'], 'meta_lead_whatsapp_logs_lead_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_lead_whatsapp_message_logs');
    }
};
