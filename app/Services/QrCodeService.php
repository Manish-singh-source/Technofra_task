<?php

namespace App\Services;

use App\Models\VCardContact;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class QrCodeService
{
    public function generate(VCardContact $contact, string $payload): void
    {
        if (strlen($payload) > 2200) throw new RuntimeException('This contact is too large for a reliably scannable QR code. Shorten notes or optional fields.');
        $qr = QrCode::create($payload)->setSize(1000)->setMargin(4)->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium);
        $base = 'vcards/'.$contact->id.'/contact-'.now()->format('YmdHis');
        $png = $base.'.png'; $svg = $base.'.svg';
        Storage::disk('public')->put($png, (new PngWriter())->write($qr)->getString());
        Storage::disk('public')->put($svg, (new SvgWriter())->write($qr)->getString());
        $contact->qrCode()->updateOrCreate([], ['png_path' => $png, 'svg_path' => $svg, 'qr_format' => 'vcard-3.0', 'generated_at' => now()]);
    }
}
