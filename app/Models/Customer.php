<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_name',
        'contact_person',
        'email',
        'phone',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'client_type',
        'industry',
        'status',
        'priority_level',
        'assigned_manager_id',
        'default_due_days',
        'billing_type',
        'role',
        'password',
    ];

    /**
     * Get the user associated with the customer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the customer.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
