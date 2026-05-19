<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobapplication', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fname', 150);
            $table->string('email', 150);
            $table->string('contact', 25);
            $table->string('role', 150);
            $table->string('experience', 100);
            $table->string('ctc', 100);
            $table->string('ectc', 100);
            $table->string('location', 150);
            $table->text('skills_text');
            $table->longText('skills_json')->nullable();
            $table->text('ai_tools_text');
            $table->longText('ai_tools_json')->nullable();
            $table->string('notice', 120);
            $table->string('rn', 150)->default('');
            $table->string('refrence', 150);
            $table->string('resume_file', 255);
            $table->string('portfolio_link', 255)->default('');
            $table->string('source_page', 120)->default('job-application.php');
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobapplication');
    }
};

