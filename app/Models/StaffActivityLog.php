<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'staff_name',
        'staff_email',
        'session_id',
        'ip_address',
        'user_agent',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getDeviceNameAttribute(): string
    {
        $agent = strtolower((string) $this->user_agent);

        if (str_contains($agent, 'ipad')) {
            return 'iPad';
        }

        if (str_contains($agent, 'iphone')) {
            return 'iPhone';
        }

        if (str_contains($agent, 'android') && str_contains($agent, 'mobile')) {
            return 'Android phone';
        }

        if (str_contains($agent, 'tablet')) {
            return 'Tablet';
        }

        if (str_contains($agent, 'android')) {
            return 'Android device';
        }

        if (str_contains($agent, 'windows')) {
            return 'Windows desktop';
        }

        if (str_contains($agent, 'macintosh') || str_contains($agent, 'mac os')) {
            return 'Mac desktop';
        }

        if (str_contains($agent, 'linux')) {
            return 'Linux desktop';
        }

        return $agent === '' ? 'Unknown device' : 'Other device';
    }

    public function getBrowserNameAttribute(): string
    {
        $agent = strtolower((string) $this->user_agent);

        if (str_contains($agent, 'edg/')) {
            return 'Microsoft Edge';
        }

        if (str_contains($agent, 'opr/') || str_contains($agent, 'opera')) {
            return 'Opera';
        }

        if (str_contains($agent, 'firefox/')) {
            return 'Firefox';
        }

        if (str_contains($agent, 'chrome/')) {
            return 'Google Chrome';
        }

        if (str_contains($agent, 'safari/')) {
            return 'Safari';
        }

        return 'Unknown browser';
    }
}
