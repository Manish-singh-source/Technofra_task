<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['client_id', 'name', 'website_url', 'industry', 'target_country', 'target_locations', 'target_language', 'search_engine', 'devices', 'start_date', 'goals', 'reporting_frequency', 'status', 'created_by'];
    protected $casts = ['target_locations' => 'array', 'devices' => 'array', 'start_date' => 'date'];

    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function members() { return $this->hasMany(SeoCampaignMember::class); }
    public function audit() { return $this->hasOne(SeoAudit::class); }
    public function baseline() { return $this->hasOne(SeoBaseline::class); }
    public function competitors() { return $this->hasMany(SeoCompetitor::class); }
    public function keywords() { return $this->hasMany(SeoKeyword::class); }
    public function cycles() { return $this->hasMany(SeoMonthlyCycle::class); }
    public function activities() { return $this->hasMany(SeoActivity::class); }
    public function performanceRecords() { return $this->hasMany(SeoPerformanceRecord::class); }
    public function reports() { return $this->hasMany(SeoReport::class); }
}
