<?php

namespace App\Services;

use App\Models\VCardContact;

class VCardService
{
    public function make(VCardContact $contact): string
    {
        $line = fn (string $name, $value) => filled($value) ? $name.':'.$this->escape((string) $value) : null;
        $name = implode(';', array_map(fn ($value) => $this->escape((string) $value), [$contact->last_name, $contact->first_name, $contact->middle_name, '', '']));
        $lines = ['BEGIN:VCARD', 'VERSION:3.0', 'N:'.$name, $line('FN', $contact->display_name ?: $contact->full_name)];
        foreach ([['ORG', $contact->company_name], ['TITLE', $contact->designation], ['ROLE', $contact->department]] as [$field, $value]) $lines[] = $line($field, $value);
        foreach (['mobile_number' => 'CELL', 'alternate_mobile' => 'CELL', 'office_telephone' => 'WORK', 'home_telephone' => 'HOME', 'fax_number' => 'FAX'] as $field => $type) if (filled($contact->$field)) $lines[] = 'TEL;TYPE='.$type.':'.$this->escape($contact->$field);
        foreach (['primary_email', 'secondary_email', 'additional_email'] as $field) if (filled($contact->$field)) $lines[] = 'EMAIL;TYPE=INTERNET:'.$this->escape($contact->$field);
        foreach ([['URL', $contact->company_website], ['URL;TYPE=LinkedIn', $contact->linkedin_url], ['URL;TYPE=Facebook', $contact->facebook_url], ['URL;TYPE=Instagram', $contact->instagram_url]] as [$field, $value]) $lines[] = $line($field, $value);
        if (filled($contact->whatsapp_number)) $lines[] = 'TEL;TYPE=WHATSAPP:'.$this->escape($contact->whatsapp_number);
        // ADR components: PO box; extended address; street; locality; region; postal code; country.
        $address = ['', $contact->address_line_2 ?: $contact->landmark, $contact->address_line_1, $contact->city, $contact->state, $contact->postal_code, $contact->country];
        if (collect($address)->filter()->isNotEmpty()) $lines[] = 'ADR;TYPE=WORK:'.implode(';', array_map(fn ($value) => $this->escape((string) $value), $address));
        $lines[] = $line('NOTE', $contact->notes);
        $lines[] = 'END:VCARD';
        return implode("\r\n", array_map([$this, 'fold'], array_filter($lines)))."\r\n";
    }

    private function escape(string $value): string { return str_replace(["\\", ';', ',', "\r\n", "\n", "\r"], ["\\\\", '\\;', '\\,', '\\n', '\\n', '\\n'], $value); }
    private function fold(string $line): string { return preg_replace('/(.{1,75})(?=.|$)/u', "$1\r\n ", $line) ? rtrim((string) preg_replace('/(.{1,75})(?=.|$)/u', "$1\r\n ", $line), " \r\n") : $line; }
}
