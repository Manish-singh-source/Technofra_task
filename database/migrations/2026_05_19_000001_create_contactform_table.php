<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactform', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fname', 150);
            $table->string('lname', 150);
            $table->string('contact', 25);
            $table->string('email', 150);
            $table->text('massage');
            $table->string('source_page', 120)->default('contact.php');
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactform');
    }
};

