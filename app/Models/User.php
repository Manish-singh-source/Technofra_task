<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'status',
        'role',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getNameAttribute(): string
    {
        return trim(collect([$this->first_name, $this->last_name])->filter()->implode(' '));
    }

    public function setNameAttribute($value): void
    {
        $value = trim((string) $value);
        $parts = preg_split('/\s+/', $value, 2) ?: [];

        $this->attributes['first_name'] = $parts[0] ?? '';
        $this->attributes['last_name'] = $parts[1] ?? '';
    }

    /**
     * Get the staff record associated with the user.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    /**
     * Get the customer record associated with the user.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Check if user is a staff member.
     */
    public function isStaff()
    {
        return !$this->isCustomer() && !empty($this->role);
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer()
    {
        return $this->customer()->exists();
    }

    /**
     * Get all permissions for the user (including role permissions).
     */
    public function getAllPermissionsAttribute()
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Get user type (staff, customer, or admin).
     */
    public function getUserTypeAttribute()
    {
        if ($this->isCustomer()) {
            return 'customer';
        }

        if ($this->isStaff()) {
            return 'staff';
        }

        return 'admin';
    }

    // Departments using pivot table
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'staff_department')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'staff_team')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function projects()
    {
        return Project::whereJsonContains('members', (string) $this->id);
    }

    public function tasks()
    {
        return Task::whereJsonContains('assignees', (string) $this->id)
            ->orWhereJsonContains('followers', (string) $this->id);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    // Clients Roles 

    /**
     * Get the services for the client.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'client_id', 'id');
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'id');
    }

    public function businessDetail()
    {
        return $this->hasOne(ClientBusinessDetail::class, 'user_id', 'id');
    }
}
