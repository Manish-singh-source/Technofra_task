<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'file_name',
        'original_name',
        'file_type',
        'file_size',
        'file_path',
        'description',
        'uploaded_by',
    ];

    /**
     * Get the project that owns the file.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file icon based on file type.
     */
    public function getFileIconAttribute()
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp'])) {
            return 'bx-image';
        } elseif (in_array($extension, ['pdf'])) {
            return 'bx-file-pdf';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            return 'bx-file-doc';
        } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
            return 'bx-file';
        } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
            return 'bx-archive';
        } elseif (in_array($extension, ['ppt', 'pptx'])) {
            return 'bx-file-present';
        } elseif (in_array($extension, ['txt', 'md'])) {
            return 'bx-file-blank';
        } else {
            return 'bx-file';
        }
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFormattedSizeAttribute()
    {
        $size = $this->file_size;
        if ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' B';
        }
    }

    /**
     * Check if the file is an image.
     */
    public function isImage(): bool
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp']);
    }

    /**
     * Check if the file is a PDF.
     */
    public function isPdf(): bool
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION)) === 'pdf';
    }
}
