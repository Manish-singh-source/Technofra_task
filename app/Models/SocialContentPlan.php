<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialContentPlan extends Model
{
    protected $fillable = ['client_id', 'plan_month', 'plan_year', 'title', 'notes', 'client_feedback', 'client_reviewed_at', 'status', 'created_by'];
    protected $casts = ['client_reviewed_at' => 'datetime'];

    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function posts() { return $this->hasMany(SocialPost::class); }
}
