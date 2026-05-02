<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('fcm_tokens')) {
            Schema::create('fcm_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('token', 191);
                $table->string('device_id')->nullable();
                $table->string('platform', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('fcm_tokens', 'token')) {
            Schema::table('fcm_tokens', function (Blueprint $table) {
                $table->string('token', 191)->change();
            });
        }

        $database = DB::getDatabaseName();

        $hasUnique = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'fcm_tokens')
            ->where('index_name', 'fcm_tokens_user_id_token_unique')
            ->exists();

        if (! $hasUnique) {
            Schema::table('fcm_tokens', function (Blueprint $table) {
                $table->unique(['user_id', 'token'], 'fcm_tokens_user_id_token_unique');
            });
        }

        $hasActiveIndex = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'fcm_tokens')
            ->where('index_name', 'fcm_tokens_user_id_is_active_index')
            ->exists();

        if (! $hasActiveIndex) {
            Schema::table('fcm_tokens', function (Blueprint $table) {
                $table->index(['user_id', 'is_active'], 'fcm_tokens_user_id_is_active_index');
            });
        }

        $hasForeign = DB::table('information_schema.key_column_usage')
            ->where('table_schema', $database)
            ->where('table_name', 'fcm_tokens')
            ->where('column_name', 'user_id')
            ->whereNotNull('referenced_table_name')
            ->exists();

        if (! $hasForeign) {
            Schema::table('fcm_tokens', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
