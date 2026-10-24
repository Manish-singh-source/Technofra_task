<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostPlatform extends Model
{
    protected $fillable = ['social_post_id', 'platform', 'published_url', 'published_at'];
    protected $casts = ['published_at' => 'datetime'];
}
