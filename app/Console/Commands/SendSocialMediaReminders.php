<?php

namespace App\Console\Commands;

use App\Models\SocialPost;
use App\Services\UnifiedNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendSocialMediaReminders extends Command
{
    protected $signature = 'social-media:send-reminders';
    protected $description = 'Send deduplicated social media deadline and client approval reminders';

    public function handle(UnifiedNotificationService $notifications): int
    {
        $today = now()->toDateString();
        $posts = SocialPost::with(['client', 'assignments.user'])->whereIn('status', ['assigned', 'in_progress', 'internal_review', 'pending_client_approval', 'client_changes_requested'])->get();
        foreach ($posts as $post) {
            if ($post->status === 'pending_client_approval' && $post->client) $this->sendOnce($post->id, $post->client->id, 'client_approval', $today, $notifications, $post, 'Client approval pending', "Please review {$post->title}.");
            if ($post->internal_deadline_at && $post->internal_deadline_at->toDateString() <= $today) foreach ($post->assignments as $assignment) $this->sendOnce($post->id, $assignment->user_id, 'internal_deadline', $today, $notifications, $post, 'Social media deadline', "{$post->title} is due today or overdue.");
        }
        return self::SUCCESS;
    }

    private function sendOnce(int $postId, int $userId, string $type, string $date, UnifiedNotificationService $notifications, SocialPost $post, string $title, string $body): void
    {
        $created = DB::table('social_post_reminders')->insertOrIgnore(['social_post_id' => $postId, 'user_id' => $userId, 'reminder_type' => $type, 'reminder_date' => $date, 'sent_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        if ($created) $notifications->sendToUser($post->assignments->firstWhere('user_id', $userId)?->user ?? $post->client, $title, $body, 'social_media', ['post_id' => $postId]);
    }
}
