<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SeoAudit extends Model { protected $fillable = ['seo_campaign_id','status','summary','prepared_by','reviewed_by','submitted_at','reviewed_at','review_feedback']; protected $casts=['submitted_at'=>'datetime','reviewed_at'=>'datetime']; public function campaign(){return $this->belongsTo(SeoCampaign::class,'seo_campaign_id');} public function findings(){return $this->hasMany(SeoAuditFinding::class);} }
