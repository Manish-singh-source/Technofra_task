<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SeoBaseline extends Model { protected $fillable=['seo_campaign_id','seo_audit_id','captured_on','status','captured_by','approved_by','approved_at','notes']; protected $casts=['captured_on'=>'date','approved_at'=>'datetime']; public function campaign(){return $this->belongsTo(SeoCampaign::class,'seo_campaign_id');} public function metrics(){return $this->hasMany(SeoBaselineMetric::class);} }
