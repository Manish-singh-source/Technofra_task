<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostApproval extends Model
{
    protected $fillable = ['social_post_id', 'social_post_version_id', 'decided_by', 'stage', 'decision', 'comment'];
    public function version() { return $this->belongsTo(SocialPostVersion::class, 'social_post_version_id'); }
    public function decider() { return $this->belongsTo(User::class, 'decided_by'); }
}
