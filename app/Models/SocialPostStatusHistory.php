<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostStatusHistory extends Model
{
    protected $fillable = ['social_post_id', 'user_id', 'from_status', 'to_status', 'action', 'comment'];
    public function user() { return $this->belongsTo(User::class); }
}
