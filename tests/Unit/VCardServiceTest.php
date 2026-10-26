<?php

namespace Tests\Unit;

use App\Models\VCardContact;
use App\Services\VCardService;
use Tests\TestCase;

class VCardServiceTest extends TestCase
{
    public function test_it_builds_a_crlf_terminated_vcard_three_payload(): void
    {
        $contact = new VCardContact([
            'first_name' => 'રાહુલ', 'last_name' => 'Sharma', 'mobile_number' => '+919876543210',
            'primary_email' => 'rahul@example.com', 'company_name' => 'Technofra, Inc.', 'notes' => 'Line one; line two',
        ]);
        $vcard = (new VCardService())->make($contact);

        $this->assertStringStartsWith("BEGIN:VCARD\r\nVERSION:3.0\r\n", $vcard);
        $this->assertStringContainsString("N:Sharma;રાહુલ;;;", $vcard);
        $this->assertStringContainsString('TEL;TYPE=CELL:+919876543210', $vcard);
        $this->assertStringContainsString('ORG:Technofra\\, Inc.', $vcard);
        $this->assertStringContainsString('NOTE:Line one\\; line two', $vcard);
        $this->assertStringEndsWith("END:VCARD\r\n", $vcard);
    }

    public function test_qr_relation_uses_the_migrated_foreign_key_name(): void
    {
        $this->assertSame('vcard_contact_id', (new VCardContact())->qrCode()->getForeignKeyName());
    }
}
