<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads') && ! Schema::hasColumn('leads', 'created_by')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('requirements');
                $table->index('created_by', 'leads_created_by_idx');
            });
        } elseif (Schema::hasTable('leads') && Schema::hasColumn('leads', 'created_by') && ! $this->indexExists('leads', 'leads_created_by_idx')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->index('created_by', 'leads_created_by_idx');
            });
        }

        if (! Schema::hasTable('settings')) {
            return;
        }

        if (! Schema::hasColumn('settings', 'user_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id', 'settings_user_id_idx');
            });
        }

        if ($this->indexExists('settings', 'settings_key_unique')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique('settings_key_unique');
            });
        }

        if (! $this->indexExists('settings', 'settings_user_id_key_unique')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique(['user_id', 'key'], 'settings_user_id_key_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            if ($this->indexExists('settings', 'settings_user_id_key_unique')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->dropUnique('settings_user_id_key_unique');
                });
            }

            if (! $this->indexExists('settings', 'settings_key_unique')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->unique('key', 'settings_key_unique');
                });
            }

            if (Schema::hasColumn('settings', 'user_id')) {
                Schema::table('settings', function (Blueprint $table) {
                    if ($this->indexExists('settings', 'settings_user_id_idx')) {
                        $table->dropIndex('settings_user_id_idx');
                    }
                    $table->dropColumn('user_id');
                });
            }
        }

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'created_by') && $this->indexExists('leads', 'leads_created_by_idx')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropIndex('leads_created_by_idx');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
