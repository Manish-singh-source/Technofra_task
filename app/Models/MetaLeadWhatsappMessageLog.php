<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaLeadWhatsappMessageLog extends Model
{
    use HasFactory;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'meta_lead_id',
        'phone',
        'template_name',
        'template_parameters',
        'status',
        'provider_status_code',
        'provider_response',
        'error_message',
        'attempted_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'template_parameters' => 'array',
        'provider_response' => 'array',
        'attempted_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function metaLead()
    {
        return $this->belongsTo(MetaLead::class);
    }
}
