<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_leads', function (Blueprint $table) {
            $table->string('welcome_whatsapp_status', 20)->default('pending')->after('status');
            $table->timestamp('welcome_whatsapp_sent_at')->nullable()->after('welcome_whatsapp_status');
            $table->foreignId('welcome_whatsapp_last_log_id')
                ->nullable()
                ->after('welcome_whatsapp_sent_at')
                ->constrained('meta_lead_whatsapp_message_logs')
                ->nullOnDelete();
            $table->unsignedInteger('welcome_whatsapp_attempts')->default(0)->after('welcome_whatsapp_last_log_id');

            $table->index('welcome_whatsapp_status', 'meta_leads_welcome_whatsapp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('meta_leads', function (Blueprint $table) {
            $table->dropForeign(['welcome_whatsapp_last_log_id']);
            $table->dropIndex('meta_leads_welcome_whatsapp_status_idx');
            $table->dropColumn([
                'welcome_whatsapp_status',
                'welcome_whatsapp_sent_at',
                'welcome_whatsapp_last_log_id',
                'welcome_whatsapp_attempts',
            ]);
        });
    }
};
