<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('project_milestones')) {
            return;
        }

        Schema::table('project_milestones', function (Blueprint $table) {
            if (! Schema::hasColumn('project_milestones', 'progress_percentage')) {
                $table->unsignedTinyInteger('progress_percentage')->nullable()->after('completed_at');
                $table->index('progress_percentage');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('project_milestones')) {
            return;
        }

        Schema::table('project_milestones', function (Blueprint $table) {
            if (Schema::hasColumn('project_milestones', 'progress_percentage')) {
                $table->dropIndex(['progress_percentage']);
                $table->dropColumn('progress_percentage');
            }
        });
    }
};
