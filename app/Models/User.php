<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles {
        hasPermissionTo as protected spatieHasPermissionTo;
        checkPermissionTo as protected spatieCheckPermissionTo;
        getAllPermissions as protected spatieGetAllPermissions;
        hasRole as protected spatieHasRole;
    }

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
     * Some existing records store the assigned role only in users.role.
     * Treat that column as a fallback Spatie role so permissions still work.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->spatieHasPermissionTo($permission, $guardName)) {
            return true;
        }

        $role = $this->roleColumnRole($guardName);

        return $role ? $role->hasPermissionTo($permission, $guardName) : false;
    }

    public function checkPermissionTo($permission, $guardName = null): bool
    {
        try {
            return $this->hasPermissionTo($permission, $guardName);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return false;
        }
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $permissions = $this->spatieGetAllPermissions();
        $role = $this->roleColumnRole();

        if ($role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->sort()->values();
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        if ($this->spatieHasRole($roles, $guard)) {
            return true;
        }

        if (! $this->role) {
            return false;
        }

        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = explode('|', $roles);
        }

        if ($roles instanceof \BackedEnum) {
            $roles = $roles->value;
        }

        if (is_string($roles)) {
            return $roles === $this->role;
        }

        if (is_array($roles) || $roles instanceof \Illuminate\Support\Collection) {
            return collect($roles)->contains(fn($role) => $role === $this->role);
        }

        if ($roles instanceof Role) {
            return $roles->name === $this->role;
        }

        return false;
    }

    protected function roleColumnRole(?string $guardName = null): ?Role
    {
        if (! $this->role) {
            return null;
        }

        return Role::query()
            ->where('name', $this->role)
            ->when($guardName, fn($query) => $query->where('guard_name', $guardName))
            ->with('permissions')
            ->first();
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
        return Task::whereJsonContains('assignees', (int) $this->id)
            ->orWhereJsonContains('followers', (int) $this->id);
    }

    public function scopeStaffMembers($query)
    {
        return $query->where('role', '!=', 'client')->whereNotNull('role');
    }

    public function assignedLeads()
    {
        return Lead::query()->where(function ($query) {
            $query->whereJsonContains('assigned', $this->id)
                ->orWhereJsonContains('assigned', (string) $this->id);
        });
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getClientNameAttribute(): string
    {
        return $this->name;
    }

    public function getContactPersonAttribute(): string
    {
        return $this->name;
    }

    public function getCnameAttribute(): string
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
