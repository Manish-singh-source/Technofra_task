<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquiry_forms', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('company', 180);
            $table->string('contact', 25);
            $table->string('designation', 180);
            $table->string('delivery_time', 120);
            $table->string('nature_of_project', 120);
            $table->text('message');
            $table->string('source_page', 120)->default('enquirynow.php');

            // Matches: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enquiry_forms');
    }
};
