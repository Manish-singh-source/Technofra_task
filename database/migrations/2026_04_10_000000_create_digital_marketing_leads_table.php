<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('digital_marketing_leads')) {
            return;
        }

        Schema::create('digital_marketing_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 25);
            $table->string('company', 150)->default('');
            $table->string('website', 255)->default('');
            $table->string('source_page', 120)->default('digitalmarketingad.php');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_marketing_leads');
    }
};