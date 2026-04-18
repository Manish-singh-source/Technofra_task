<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'position',
        'website',
        'address',
        'city',
        'state',
        'country',
        'zipCode',
        'lead_value',
        'source',
        'assigned',
        'tags',
        'description',
        'status',
    ];

    protected $casts = [
        'assigned' => 'array',
        'tags' => 'array',
        'lead_value' => 'decimal:2',
    ];

    /**
     * Get staff names for assigned staff IDs
     * This is a helper method, not a relationship
     */
    public function getAssignedStaffNamesAttribute()
    {
        if (!$this->assigned || !is_array($this->assigned)) {
            return collect([]);
        }
        
        return User::staffMembers()
            ->whereIn('id', $this->assigned)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function assignedUsers()
    {
        if (!$this->assigned || !is_array($this->assigned)) {
            return collect([]);
        }

        return User::staffMembers()
            ->whereIn('id', $this->assigned)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }
}
