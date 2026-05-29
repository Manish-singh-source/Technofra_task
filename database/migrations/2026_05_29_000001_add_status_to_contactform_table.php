<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactform', function (Blueprint $table) {
            if (! Schema::hasColumn('contactform', 'status')) {
                $table->string('status', 50)
                    ->default('new')
                    ->after('source_page');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contactform', function (Blueprint $table) {
            if (Schema::hasColumn('contactform', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
