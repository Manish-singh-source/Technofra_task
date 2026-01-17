<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_image',
        'first_name',
        'last_name',
        'email',
        'phone',
        'role',
        'password',
        'status',
        'departments',
    ];

    protected $casts = [
        'departments' => 'array',
    ];
}
