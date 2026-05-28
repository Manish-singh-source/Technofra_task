<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (! Schema::hasColumn('projects', 'lifecycle_stage')) {
                    $table->string('lifecycle_stage')->nullable()->after('status');
                    $table->index('lifecycle_stage');
                }
            });
        }

        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('tasks', 'workflow_status')) {
                    $table->string('workflow_status')->nullable()->after('status');
                    $table->index('workflow_status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'workflow_status')) {
                    $table->dropIndex(['workflow_status']);
                    $table->dropColumn('workflow_status');
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'lifecycle_stage')) {
                    $table->dropIndex(['lifecycle_stage']);
                    $table->dropColumn('lifecycle_stage');
                }
            });
        }
    }
};
