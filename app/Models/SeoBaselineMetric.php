<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SeoBaselineMetric extends Model { protected $fillable=['seo_baseline_id','metric_key','metric_value','unit','source','period_start','period_end']; protected $casts=['period_start'=>'date','period_end'=>'date','metric_value'=>'decimal:4']; public function baseline(){return $this->belongsTo(SeoBaseline::class,'seo_baseline_id');} }
