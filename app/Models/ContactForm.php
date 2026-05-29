<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contactform';

    public $timestamps = false;

    protected $fillable = [
        'fname',
        'lname',
        'contact',
        'email',
        'massage',
        'source_page',
        'status',
        'deleted_at',
    ];
}
