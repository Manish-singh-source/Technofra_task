<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('webapp_leads')) {
            return;
        }

        Schema::create('webapp_leads', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->increments('id');
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 25);
            $table->string('company', 150)->default('');
            $table->string('website', 255)->default('');
            $table->text('message')->nullable();
            $table->string('source_page', 120)->default('webapp.php');
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webapp_leads');
    }
};
