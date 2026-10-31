<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;

class DigitalCardController extends Controller
{
    public function create()
    {
        return view('digital-cards.create');
    }

    public function exportHtml(Request $request)
    {
        $request->validate(['html' => ['required', 'string'], 'css' => ['required', 'string'], 'images.*' => ['nullable', 'image', 'max:5120']]);
        $file = tempnam(sys_get_temp_dir(), 'digital-card-');
        $zip = new ZipArchive();
        abort_unless($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Unable to create ZIP file.');
        $zip->addFromString('index.html', $request->string('html')->toString());
        $zip->addFromString('style.css', $request->string('css')->toString());
        foreach ($request->file('images', []) as $image) {
            if ($image && $image->isValid()) $zip->addFile($image->getRealPath(), 'images/'.basename($image->getClientOriginalName()));
        }
        $zip->close();
        return response()->download($file, 'digital-card-html.zip')->deleteFileAfterSend(true);
    }
}
