<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactform', function (Blueprint $table) {
            if (! Schema::hasColumn('contactform', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('created_at');
            }
        });

        Schema::table('jobapplication', function (Blueprint $table) {
            if (! Schema::hasColumn('jobapplication', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contactform', function (Blueprint $table) {
            if (Schema::hasColumn('contactform', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });

        Schema::table('jobapplication', function (Blueprint $table) {
            if (Schema::hasColumn('jobapplication', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};

