<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileUpload
{
    public static function fileUpload(?UploadedFile $file, string $path = ''): string
    {
        if (! $file instanceof UploadedFile) {
            return '';
        }

        $path = self::normalizePath($path !== '' ? $path : 'uploads/');

        $fullPath = public_path($path);
        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        $filename = self::generateFilename($file);
        $file->move($fullPath, $filename);

        return $path.$filename;
    }

    public static function updateFileUpload(?UploadedFile $file, string $oldFilePath = '', string $path = ''): string
    {
        if (! $file instanceof UploadedFile) {
            return $oldFilePath;
        }

        self::deleteFile($oldFilePath);

        return self::fileUpload($file, $path);
    }

    public static function deleteFile(?string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }

        $localPath = public_path($filePath);
        if (! File::exists($localPath)) {
            return false;
        }

        return File::delete($localPath);
    }

    protected static function normalizePath(string $path): string
    {
        return rtrim(trim($path), '/').'/';
    }

    protected static function generateFilename(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $slug = Str::slug($name);
        $slug = $slug !== '' ? $slug : 'file';

        return $slug.'-'.time().'-'.Str::lower(Str::random(8)).($extension ? '.'.$extension : '');
    }
}
