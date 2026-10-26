<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VCardQrCode extends Model
{
    protected $table = 'vcard_qr_codes';

    protected $fillable = ['png_path', 'svg_path', 'qr_format', 'qr_version', 'generated_at'];
    protected $casts = ['generated_at' => 'datetime'];
    public function contact() { return $this->belongsTo(VCardContact::class, 'vcard_contact_id'); }
}
