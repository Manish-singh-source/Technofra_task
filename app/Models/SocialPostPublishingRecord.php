<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostPublishingRecord extends Model
{
    protected $fillable = ['social_post_id', 'published_by', 'status', 'scheduled_at', 'actual_published_at', 'published_url', 'proof_path', 'reason'];
    protected $casts = ['scheduled_at' => 'datetime', 'actual_published_at' => 'datetime'];
}
