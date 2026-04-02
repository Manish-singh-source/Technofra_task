<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bookcall') || Schema::hasColumn('bookcall', 'meeting_agenda')) {
            return;
        }

        Schema::table('bookcall', function (Blueprint $table) {
            $table->text('meeting_agenda')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookcall') || ! Schema::hasColumn('bookcall', 'meeting_agenda')) {
            return;
        }

        Schema::table('bookcall', function (Blueprint $table) {
            $table->dropColumn('meeting_agenda');
        });
    }
};
