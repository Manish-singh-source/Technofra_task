<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadPipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'color', 'stage_order', 'is_default', 'is_closed',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_closed' => 'boolean',
    ];
}
