<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'project_code')) {
                $table->string('project_code')->nullable()->after('project_name');
                $table->index('project_code');
            }

            if (! Schema::hasColumn('projects', 'project_type')) {
                $table->string('project_type')->nullable()->after('priority');
                $table->index('project_type');
            }

            if (! Schema::hasColumn('projects', 'actual_hours')) {
                $table->decimal('actual_hours', 10, 2)->nullable()->after('estimated_hours');
            }

            if (! Schema::hasColumn('projects', 'progress_percentage')) {
                $table->unsignedTinyInteger('progress_percentage')->nullable()->after('actual_hours');
            }

            if (! Schema::hasColumn('projects', 'project_manager_id')) {
                $table->foreignId('project_manager_id')->nullable()->after('members')->constrained('users')->nullOnDelete();
                $table->index('project_manager_id');
            }

            if (! Schema::hasColumn('projects', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('project_manager_id')->constrained('users')->nullOnDelete();
                $table->index('approved_by');
            }

            if (! Schema::hasColumn('projects', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('projects', 'deployment_date')) {
                $table->date('deployment_date')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('projects', 'maintenance_expiry')) {
                $table->date('maintenance_expiry')->nullable()->after('deployment_date');
            }

            if (! Schema::hasColumn('projects', 'health_status')) {
                $table->string('health_status')->nullable()->after('maintenance_expiry');
                $table->index('health_status');
            }

            if (! Schema::hasColumn('projects', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('health_status');
                $table->index('last_activity_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'last_activity_at')) {
                $table->dropIndex(['last_activity_at']);
                $table->dropColumn('last_activity_at');
            }

            if (Schema::hasColumn('projects', 'health_status')) {
                $table->dropIndex(['health_status']);
                $table->dropColumn('health_status');
            }

            if (Schema::hasColumn('projects', 'maintenance_expiry')) {
                $table->dropColumn('maintenance_expiry');
            }

            if (Schema::hasColumn('projects', 'deployment_date')) {
                $table->dropColumn('deployment_date');
            }

            if (Schema::hasColumn('projects', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('projects', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('projects', 'project_manager_id')) {
                $table->dropConstrainedForeignId('project_manager_id');
            }

            if (Schema::hasColumn('projects', 'progress_percentage')) {
                $table->dropColumn('progress_percentage');
            }

            if (Schema::hasColumn('projects', 'actual_hours')) {
                $table->dropColumn('actual_hours');
            }

            if (Schema::hasColumn('projects', 'project_type')) {
                $table->dropIndex(['project_type']);
                $table->dropColumn('project_type');
            }

            if (Schema::hasColumn('projects', 'project_code')) {
                $table->dropIndex(['project_code']);
                $table->dropColumn('project_code');
            }
        });
    }
};
