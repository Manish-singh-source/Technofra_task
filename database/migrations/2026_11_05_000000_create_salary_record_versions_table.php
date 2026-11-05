<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_record_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->string('reason', 500)->nullable();
            $table->json('data');
            $table->timestamps();
            $table->index(['salary_record_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_record_versions');
    }
};
