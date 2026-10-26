<?php

namespace Tests\Unit;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use PHPUnit\Framework\TestCase;

class QrCodeLibraryTest extends TestCase
{
    public function test_it_generates_png_and_svg_qr_images(): void
    {
        $qr = QrCode::create("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:Rahul Sharma\r\nEND:VCARD\r\n")
            ->setSize(300)->setMargin(4)->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium);
        $this->assertStringStartsWith("\x89PNG", (new PngWriter())->write($qr)->getString());
        $this->assertStringContainsString('<svg', (new SvgWriter())->write($qr)->getString());
    }
}
