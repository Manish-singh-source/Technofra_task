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

    public static function generateAvatar(string $name, string $path = 'uploads/avatars/', ?string $oldFilePath = null): string
    {
        $path = self::normalizePath($path !== '' ? $path : 'uploads/avatars/');

        $fullPath = public_path($path);
        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        if (! empty($oldFilePath)) {
            self::deleteFile($oldFilePath);
        }

        $filename = self::generateAvatarFilename($name);
        app('avatar')->create($name)->save($fullPath.$filename);

        return $path.$filename;
    }

    public static function uploadOrGenerateAvatar(
        ?UploadedFile $file,
        string $name,
        string $path,
        ?string $oldFilePath = null
    ): string {
        if ($file instanceof UploadedFile) {
            return self::updateFileUpload($file, (string) $oldFilePath, $path);
        }

        if (! empty($oldFilePath)) {
            return $oldFilePath;
        }

        return self::generateAvatar($name, $path, $oldFilePath);
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

    protected static function generateAvatarFilename(string $name): string
    {
        $slug = Str::slug($name);
        $slug = $slug !== '' ? $slug : 'avatar';

        return $slug.'-'.time().'-'.Str::lower(Str::random(8)).'.png';
    }
}
