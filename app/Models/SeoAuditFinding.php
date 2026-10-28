<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SeoAuditFinding extends Model { protected $fillable=['seo_audit_id','category','title','affected_url','description','severity','recommended_solution','assigned_to','status','resolved_at']; protected $casts=['resolved_at'=>'datetime']; public function audit(){return $this->belongsTo(SeoAudit::class,'seo_audit_id');} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
