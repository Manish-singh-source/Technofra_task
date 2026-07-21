<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaLead extends Model
{
    use HasFactory;

    public const WELCOME_WHATSAPP_PENDING = 'pending';
    public const WELCOME_WHATSAPP_QUEUED = 'queued';
    public const WELCOME_WHATSAPP_SENT = 'sent';
    public const WELCOME_WHATSAPP_FAILED = 'failed';
    public const WELCOME_WHATSAPP_SKIPPED = 'skipped';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'form_id',
        'page_id',
        'ad_id',
        'full_name',
        'email',
        'phone',
        'city',
        'state',
        'field_data',
        'created_time',
        'status',
        'welcome_whatsapp_status',
        'welcome_whatsapp_sent_at',
        'welcome_whatsapp_last_log_id',
        'welcome_whatsapp_attempts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'field_data' => 'array',
        'created_time' => 'datetime',
        'welcome_whatsapp_sent_at' => 'datetime',
        'welcome_whatsapp_attempts' => 'integer',
    ];

    public function whatsappMessageLogs()
    {
        return $this->hasMany(MetaLeadWhatsappMessageLog::class);
    }

    public function latestWelcomeWhatsappLog()
    {
        return $this->belongsTo(MetaLeadWhatsappMessageLog::class, 'welcome_whatsapp_last_log_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'meta_lead_user')
            ->withPivot(['page_id', 'form_id'])
            ->withTimestamps();
    }
}
