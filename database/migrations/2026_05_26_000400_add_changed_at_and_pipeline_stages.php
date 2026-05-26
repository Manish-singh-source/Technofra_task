<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_status_histories') && ! Schema::hasColumn('lead_status_histories', 'changed_at')) {
            Schema::table('lead_status_histories', function (Blueprint $table) {
                $table->dateTime('changed_at')->nullable()->after('remarks');
                $table->index('changed_at', 'lead_status_histories_changed_at_idx');
            });
        }

        if (! Schema::hasTable('lead_pipeline_stages')) {
            Schema::create('lead_pipeline_stages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color')->default('secondary');
                $table->unsignedInteger('stage_order')->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_closed')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_pipeline_stages');
    }
};
