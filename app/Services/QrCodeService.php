<?php

namespace App\Services;

use App\Models\VCardContact;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\File;
use RuntimeException;

class QrCodeService
{
    public function generate(VCardContact $contact, string $payload): void
    {
        if (strlen($payload) > 2200) throw new RuntimeException('This contact is too large for a reliably scannable QR code. Shorten notes or optional fields.');
        $qr = QrCode::create($payload)->setSize(1000)->setMargin(4)->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium);
        // Keep QR files with the application's existing public uploads. This
        // avoids relying on Laravel's storage symlink, which is unavailable on
        // some shared-hosting PHP configurations.
        $base = 'uploads/vcards/'.$contact->id.'/contact-'.now()->format('YmdHis');
        $png = $base.'.png'; $svg = $base.'.svg';
        File::ensureDirectoryExists(public_path(dirname($png)));
        File::put(public_path($png), (new PngWriter())->write($qr)->getString());
        File::put(public_path($svg), (new SvgWriter())->write($qr)->getString());
        $contact->qrCode()->updateOrCreate([], ['png_path' => $png, 'svg_path' => $svg, 'qr_format' => 'vcard-3.0', 'generated_at' => now()]);
    }
}
