<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostComment extends Model
{
    protected $fillable = ['social_post_id', 'social_post_version_id', 'parent_id', 'user_id', 'comment', 'is_internal', 'is_resolved'];
    protected $casts = ['is_internal' => 'boolean', 'is_resolved' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
}
