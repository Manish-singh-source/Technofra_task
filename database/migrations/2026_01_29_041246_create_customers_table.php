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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->enum('client_type', ['Individual', 'Company', 'Organization']);
            $table->string('industry');
            $table->enum('status', ['Active', 'Inactive', 'Suspended']);
            $table->enum('priority_level', ['Low', 'Medium', 'High'])->nullable();
            $table->unsignedBigInteger('assigned_manager_id')->nullable();
            $table->integer('default_due_days')->nullable();
            $table->enum('billing_type', ['Hourly', 'Fixed', 'Retainer'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};