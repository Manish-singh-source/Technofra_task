<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VCardContact extends Model
{
    use HasFactory, SoftDeletes;

    /** The migration uses the product name as one word. */
    protected $table = 'vcard_contacts';

    protected $fillable = ['first_name','middle_name','last_name','display_name','designation','department','company_name','company_website','mobile_number','alternate_mobile','office_telephone','home_telephone','fax_number','primary_email','secondary_email','additional_email','address_line_1','address_line_2','landmark','city','state','postal_code','country','linkedin_url','facebook_url','instagram_url','whatsapp_number','notes','profile_photo','company_logo','status'];
    protected $casts = ['status' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function qrCode() { return $this->hasOne(VCardQrCode::class, 'vcard_contact_id'); }
    public function getFullNameAttribute(): string { return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' ')); }
}
