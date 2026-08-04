<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bookcall')) {
            return;
        }

        Schema::table('bookcall', function (Blueprint $table) {
            if (! Schema::hasColumn('bookcall', 'meet_link')) {
                $table->text('meet_link')->nullable()->after('meeting_agenda');
            }

            if (! Schema::hasColumn('bookcall', 'google_event_id')) {
                $table->string('google_event_id')->nullable()->after('meet_link');
            }

            if (! Schema::hasColumn('bookcall', 'meet_duration_minutes')) {
                $table->unsignedInteger('meet_duration_minutes')->nullable()->after('google_event_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookcall')) {
            return;
        }

        Schema::table('bookcall', function (Blueprint $table) {
            if (Schema::hasColumn('bookcall', 'meet_duration_minutes')) {
                $table->dropColumn('meet_duration_minutes');
            }

            if (Schema::hasColumn('bookcall', 'google_event_id')) {
                $table->dropColumn('google_event_id');
            }

            if (Schema::hasColumn('bookcall', 'meet_link')) {
                $table->dropColumn('meet_link');
            }
        });
    }
};
