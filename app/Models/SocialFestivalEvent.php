<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialFestivalEvent extends Model
{
    protected $fillable = ['name', 'event_date', 'category', 'country_region', 'recurrence_type', 'client_id', 'description', 'reference_url'];
    protected $casts = ['event_date' => 'date'];
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
}
