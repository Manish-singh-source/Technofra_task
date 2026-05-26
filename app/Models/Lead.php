<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

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
        'lead_code',
        'company_name',
        'industry',
        'priority',
        'assigned_to',
        'expected_value',
        'next_followup_at',
        'requirements',
        'created_by',
        'converted_at',
        'previous_status',
        'status_updated_at',
        'status_updated_by',
        'lost_at',
        'won_value',
        'pipeline_stage_order',
        'lost_reason',
        'assigned',
        'tags',
        'description',
        'status',
    ];

    protected $casts = [
        'assigned' => 'array',
        'tags' => 'array',
        'lead_value' => 'decimal:2',
        'expected_value' => 'decimal:2',
        'next_followup_at' => 'datetime',
        'converted_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'lost_at' => 'datetime',
        'won_value' => 'decimal:2',
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

    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class);
    }

    public function reminders()
    {
        return $this->hasMany(LeadReminder::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(LeadStatusHistory::class);
    }

    public function escalations()
    {
        return $this->hasMany(LeadEscalation::class);
    }

    public function conversions()
    {
        return $this->hasMany(LeadConversion::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function isWon(): bool
    {
        return (string) $this->status === self::STATUS_WON;
    }

    public function isLost(): bool
    {
        return (string) $this->status === self::STATUS_LOST;
    }

    public function isQualified(): bool
    {
        return (string) $this->status === self::STATUS_QUALIFIED;
    }

    public function canConvert(): bool
    {
        return ! in_array((string) $this->status, [self::STATUS_WON, self::STATUS_LOST, 'junk'], true);
    }

    public function canEdit(): bool
    {
        return ! in_array((string) $this->status, [self::STATUS_WON, self::STATUS_LOST, 'junk'], true);
    }

    public function currentStage(): ?array
    {
        return collect(config('lead_statuses', []))->firstWhere('slug', (string) $this->status);
    }

}
