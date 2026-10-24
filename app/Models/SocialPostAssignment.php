<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostAssignment extends Model
{
    protected $fillable = ['social_post_id', 'user_id', 'assignment_role'];
    public function user() { return $this->belongsTo(User::class); }
}
