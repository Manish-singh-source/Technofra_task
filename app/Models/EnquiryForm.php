<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnquiryForm extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'company',
        'contact',
        'designation',
        'delivery_time',
        'nature_of_project',
        'message',
        'source_page',
        'status',
    ];

}
