<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_content_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('social_content_plans', 'client_feedback')) {
                $table->text('client_feedback')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('social_content_plans', 'client_reviewed_at')) {
                $table->timestamp('client_reviewed_at')->nullable()->after('client_feedback');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_content_plans', function (Blueprint $table) {
            if (Schema::hasColumn('social_content_plans', 'client_reviewed_at')) $table->dropColumn('client_reviewed_at');
            if (Schema::hasColumn('social_content_plans', 'client_feedback')) $table->dropColumn('client_feedback');
        });
    }
};
