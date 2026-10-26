<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vcard_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            foreach (['first_name', 'middle_name', 'last_name', 'display_name', 'designation', 'department', 'company_name', 'company_website', 'mobile_number', 'alternate_mobile', 'office_telephone', 'home_telephone', 'fax_number', 'primary_email', 'secondary_email', 'additional_email', 'address_line_1', 'address_line_2', 'landmark', 'city', 'state', 'postal_code', 'country', 'linkedin_url', 'facebook_url', 'instagram_url', 'whatsapp_number', 'profile_photo', 'company_logo'] as $column) {
                $table->string($column)->nullable();
            }
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'created_at']);
            $table->index(['last_name', 'first_name']);
        });

        Schema::create('vcard_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vcard_contact_id')->constrained()->cascadeOnDelete();
            $table->string('png_path')->nullable();
            $table->string('svg_path')->nullable();
            $table->string('qr_format')->default('vcard-3.0');
            $table->string('qr_version')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->unique('vcard_contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vcard_qr_codes');
        Schema::dropIfExists('vcard_contacts');
    }
};
