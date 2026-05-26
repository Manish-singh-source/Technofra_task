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
        'tags',
        'description',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'lead_value' => 'decimal:2',
        'expected_value' => 'decimal:2',
        'next_followup_at' => 'datetime',
        'converted_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'lost_at' => 'datetime',
        'won_value' => 'decimal:2',
    ];

    public function assignedLead()
    {
        // Source of truth for assignments (multi staff ids) lives in assigned_leads.
        return $this->hasOne(AssignedLead::class, 'lead_id', 'id')
            ->where('lead_model', 'lead');
    }

    public function getAssignedAttribute(): array
    {
        $ids = $this->assignedLead?->staff_ids;
        if (! is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function getAssignedToAttribute(): ?int
    {
        // Backward-compatible accessor for code that still expects assigned_to.
        $ids = $this->assigned;

        return ! empty($ids) ? (int) $ids[0] : null;
    }

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
        // Primary assignee (first staff id), resolved via accessor.
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
