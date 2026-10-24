<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostVersion extends Model
{
    protected $fillable = ['social_post_id', 'version_number', 'caption_snapshot', 'hashtags_snapshot', 'publishing_snapshot', 'note', 'created_by', 'submitted_for_review_at'];
    protected $casts = ['publishing_snapshot' => 'array', 'submitted_for_review_at' => 'datetime'];
    public function files() { return $this->hasMany(SocialPostVersionFile::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
