<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        // Drop indexes first (if present), then columns.
        Schema::table('leads', function (Blueprint $table) {
            foreach (['leads_assigned_to_idx2', 'leads_assigned_to_pipeline_idx', 'leads_assigned_to_idx'] as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Throwable $e) {
                    // Ignore if index doesn't exist.
                }
            }

            if (Schema::hasColumn('leads', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }

            if (Schema::hasColumn('leads', 'assigned')) {
                $table->dropColumn('assigned');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('priority');
                $table->index('assigned_to', 'leads_assigned_to_idx');
            }

            if (! Schema::hasColumn('leads', 'assigned')) {
                $table->json('assigned')->nullable()->after('lost_reason');
            }
        });
    }
};

