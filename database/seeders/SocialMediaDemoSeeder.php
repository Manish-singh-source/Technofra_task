<?php

namespace Database\Seeders;

use App\Models\SocialContentPlan;
use App\Models\SocialFestivalEvent;
use App\Models\SocialPost;
use App\Models\SocialPostApproval;
use App\Models\SocialPostAssignment;
use App\Models\SocialPostComment;
use App\Models\SocialPostPlatform;
use App\Models\SocialPostPublishingRecord;
use App\Models\SocialPostStatusHistory;
use App\Models\SocialPostVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SocialMediaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->clearSocialMediaData();

        $clients = User::query()->whereRaw('LOWER(role) IN (?, ?)', ['client', 'customer'])->orderBy('id')->take(2)->get();
        $staff = User::query()->whereRaw('LOWER(role) IN (?, ?)', ['staff', 'manager'])->orderBy('id')->take(4)->get();

        if ($clients->count() < 2 || $staff->isEmpty()) {
            throw new \RuntimeException('At least two client accounts and one staff account are required to seed the social media workspace.');
        }

        $today = Carbon::today()->startOfDay();
        $creator = $staff->first();
        $designer = $staff->get(1, $creator);
        $writer = $staff->get(2, $creator);
        $reviewer = $staff->get(3, $creator);

        $plans = collect([
            SocialContentPlan::create(['client_id' => $clients[0]->id, 'plan_month' => $today->month, 'plan_year' => $today->year, 'title' => 'Growth & Engagement Plan', 'notes' => 'Educational reels, product storytelling and a consistent weekly publishing cadence.', 'status' => 'client_approved', 'client_feedback' => 'Approved. Keep the tone approachable and insight-led.', 'client_reviewed_at' => $today->copy()->subDays(4), 'created_by' => $creator->id]),
            SocialContentPlan::create(['client_id' => $clients[1]->id, 'plan_month' => $today->month, 'plan_year' => $today->year, 'title' => 'Seasonal Conversion Plan', 'notes' => 'Promotion-led content paired with trust-building testimonials and festive moments.', 'status' => 'pending_client_review', 'created_by' => $creator->id]),
        ]);

        SocialFestivalEvent::insert([
            ['name' => 'New Year Campaign', 'event_date' => $today->copy()->addMonthsNoOverflow(2)->startOfYear()->toDateString(), 'category' => 'Festival', 'country_region' => 'India', 'client_id' => null, 'recurrence_type' => 'yearly', 'description' => 'Fresh-start messaging and annual offer creative.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Client Milestone Week', 'event_date' => $today->copy()->addDays(9)->toDateString(), 'category' => 'Campaign', 'country_region' => 'India', 'client_id' => $clients[0]->id, 'recurrence_type' => 'none', 'description' => 'Prepare the client anniversary story sequence and testimonial post.', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->createPost($plans[0], $clients[0], $creator, $designer, $writer, $reviewer, 'Customer story: results that matter', 'carousel', 'in_progress', $today->copy()->addDays(1)->setTime(10, 30), ['Instagram', 'LinkedIn'], 'high');
        $this->createPost($plans[0], $clients[0], $creator, $designer, $writer, $reviewer, 'Three practical tips for smarter planning', 'reel', 'pending_client_approval', $today->copy()->addDays(4)->setTime(11, 0), ['Instagram', 'Facebook'], 'medium', true);
        $this->createPost($plans[1], $clients[1], $creator, $designer, $writer, $reviewer, 'Festive offer announcement', 'image', 'scheduled', $today->copy()->addDays(7)->setTime(10, 0), ['Instagram', 'Facebook'], 'urgent', false, $today->copy()->addDays(8)->setTime(18, 0));
    }

    private function createPost(SocialContentPlan $plan, User $client, User $creator, User $designer, User $writer, User $reviewer, string $title, string $type, string $status, Carbon $plannedAt, array $platforms, string $priority, bool $withVersion = false, ?Carbon $scheduledAt = null): void
    {
        $post = SocialPost::create([
            'social_content_plan_id' => $plan->id,
            'client_id' => $client->id,
            'title' => $title,
            'content_category' => $type === 'reel' || $type === 'video' ? 'Educational' : 'Brand engagement',
            'post_type' => $type,
            'creative_brief' => 'Create a clear, on-brand social post that supports this month’s content objective and gives the audience one useful takeaway.',
            'caption' => 'A thoughtful caption written for the target audience, with a clear call to action and a consistent brand voice.',
            'hashtags' => '#BrandGrowth #ContentStrategy #SocialMedia',
            'priority' => $priority,
            'planned_at' => $plannedAt,
            'internal_deadline_at' => $plannedAt->copy()->subDay()->setTime(16, 0),
            'client_approval_deadline_at' => $plannedAt->copy()->subHours(12),
            'publishing_scheduled_at' => $scheduledAt,
            'status' => $status,
            'created_by' => $creator->id,
        ]);

        foreach ($platforms as $platform) SocialPostPlatform::create(['social_post_id' => $post->id, 'platform' => $platform]);
        foreach ([['user' => $designer, 'role' => 'designer'], ['user' => $writer, 'role' => 'writer'], ['user' => $reviewer, 'role' => 'reviewer'], ['user' => $creator, 'role' => 'publisher']] as $assignment) {
            SocialPostAssignment::create(['social_post_id' => $post->id, 'user_id' => $assignment['user']->id, 'assignment_role' => $assignment['role']]);
        }
        SocialPostStatusHistory::create(['social_post_id' => $post->id, 'user_id' => $creator->id, 'to_status' => 'planned', 'action' => 'Post created', 'comment' => 'Post added to the monthly content plan.']);
        if ($status !== 'planned') SocialPostStatusHistory::create(['social_post_id' => $post->id, 'user_id' => $creator->id, 'from_status' => 'planned', 'to_status' => $status, 'action' => str_replace('_', ' ', $status), 'comment' => 'Demo workflow moved to the current stage.']);

        if ($withVersion) {
            $version = SocialPostVersion::create(['social_post_id' => $post->id, 'version_number' => 1, 'caption_snapshot' => $post->caption, 'hashtags_snapshot' => $post->hashtags, 'publishing_snapshot' => ['platforms' => $platforms], 'note' => 'First creative concept for review.', 'created_by' => $designer->id, 'submitted_for_review_at' => $plannedAt->copy()->subDays(2)]);
            if (in_array($status, ['client_changes_requested', 'client_approved'], true)) {
                SocialPostApproval::create(['social_post_id' => $post->id, 'social_post_version_id' => $version->id, 'decided_by' => $client->id, 'stage' => 'client', 'decision' => $status === 'client_approved' ? 'approved' : 'changes_requested', 'comment' => $status === 'client_approved' ? 'Approved for publishing.' : 'Please refine the first slide headline.']);
            }
        }

        if ($scheduledAt) {
            SocialPostPublishingRecord::create(['social_post_id' => $post->id, 'published_by' => $creator->id, 'status' => $status === 'published' ? 'published' : 'scheduled', 'scheduled_at' => $scheduledAt, 'actual_published_at' => $status === 'published' ? $scheduledAt : null, 'published_url' => $status === 'published' ? 'https://example.com/social-post/'.$post->id : null]);
        }
        SocialPostComment::create(['social_post_id' => $post->id, 'user_id' => $creator->id, 'comment' => 'Keep the visual hierarchy clean and make the call to action easy to scan.', 'is_internal' => true]);
    }

    private function clearSocialMediaData(): void
    {
        foreach (['social_post_reminders', 'social_post_version_files', 'social_post_approvals', 'social_post_comments', 'social_post_versions', 'social_post_assignments', 'social_post_platforms', 'social_post_publishing_records', 'social_post_status_histories', 'social_posts', 'social_content_plans', 'social_festival_events'] as $table) {
            DB::table($table)->delete();
        }
    }
}
