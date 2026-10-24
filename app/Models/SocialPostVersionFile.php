<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostVersionFile extends Model
{
    protected $fillable = ['social_post_version_id', 'original_name', 'file_path', 'mime_type', 'file_size'];

    public function socialPostVersion()
    {
        return $this->belongsTo(SocialPostVersion::class);
    }
}
