<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model
{
    public const STATUSES = ['planned', 'assigned', 'in_progress', 'internal_review', 'internal_changes_requested', 'pending_client_approval', 'client_changes_requested', 'client_approved', 'ready_for_publishing', 'scheduled', 'published', 'completed', 'on_hold', 'rejected', 'cancelled'];

    protected $fillable = ['social_content_plan_id', 'client_id', 'project_id', 'title', 'content_category', 'post_type', 'creative_brief', 'caption', 'hashtags', 'reference_links', 'priority', 'planned_at', 'internal_deadline_at', 'client_approval_deadline_at', 'publishing_scheduled_at', 'status', 'created_by'];
    protected $casts = ['planned_at' => 'datetime', 'internal_deadline_at' => 'datetime', 'client_approval_deadline_at' => 'datetime', 'publishing_scheduled_at' => 'datetime'];

    public function plan() { return $this->belongsTo(SocialContentPlan::class, 'social_content_plan_id'); }
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function project() { return $this->belongsTo(Project::class); }
    public function platforms() { return $this->hasMany(SocialPostPlatform::class); }
    public function assignments() { return $this->hasMany(SocialPostAssignment::class); }
    public function versions() { return $this->hasMany(SocialPostVersion::class); }
    public function comments() { return $this->hasMany(SocialPostComment::class); }
    public function approvals() { return $this->hasMany(SocialPostApproval::class); }
    public function histories() { return $this->hasMany(SocialPostStatusHistory::class)->latest(); }
    public function publishingRecords() { return $this->hasMany(SocialPostPublishingRecord::class)->latest(); }
}
