<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meta_lead_user')) {
            return;
        }

        Schema::create('meta_lead_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('meta_lead_id')->constrained('meta_leads')->cascadeOnDelete();
            $table->string('page_id')->nullable();
            $table->string('form_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'meta_lead_id'], 'meta_lead_user_unique');
            $table->index(['user_id', 'page_id', 'form_id'], 'meta_lead_user_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_lead_user');
    }
};
