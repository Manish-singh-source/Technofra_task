<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SeoCampaignMember extends Model { protected $fillable = ['seo_campaign_id','user_id','campaign_role','assigned_at','unassigned_at']; protected $casts = ['assigned_at'=>'datetime','unassigned_at'=>'datetime']; public function campaign(){ return $this->belongsTo(SeoCampaign::class,'seo_campaign_id'); } public function user(){ return $this->belongsTo(User::class); } }
