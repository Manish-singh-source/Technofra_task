<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            $table->foreignId('social_festival_event_id')
                ->nullable()
                ->after('social_content_plan_id')
                ->constrained('social_festival_events')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            $table->dropForeign(['social_festival_event_id']);
            $table->dropColumn('social_festival_event_id');
        });
    }
};
