<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_content_plans', function (Blueprint $table) {
            $table->text('client_feedback')->nullable()->after('notes');
            $table->timestamp('client_reviewed_at')->nullable()->after('client_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('social_content_plans', function (Blueprint $table) {
            $table->dropColumn(['client_feedback', 'client_reviewed_at']);
        });
    }
};
