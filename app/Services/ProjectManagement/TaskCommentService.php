<?php

namespace App\Services\ProjectManagement;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskCommentAttachment;

class TaskCommentService
{
    public function create(Task $task, int $userId, string $comment, ?int $parentId = null): TaskComment
    {
        $mentions = $this->extractMentions($comment);

        return TaskComment::create([
            'task_id' => $task->id,
            'parent_id' => $parentId,
            'user_id' => $userId,
            'comment' => $comment,
            'mentions' => $mentions,
            'edit_history' => null,
            'edited_at' => null,
        ]);
    }

    public function update(TaskComment $taskComment, string $newComment): TaskComment
    {
        $history = $taskComment->edit_history ?? [];
        $history[] = [
            'comment' => $taskComment->comment,
            'edited_at' => now()->toDateTimeString(),
        ];

        $taskComment->update([
            'comment' => $newComment,
            'mentions' => $this->extractMentions($newComment),
            'edited_at' => now(),
            'edit_history' => $history,
        ]);

        return $taskComment->fresh(['user', 'attachments']);
    }

    public function storeAttachment(TaskComment $taskComment, $file): TaskCommentAttachment
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid('comment_').'_'.time().($extension ? '.'.$extension : '');
        $directory = public_path('uploads/task_comment_attachments/'.$taskComment->id);

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->move($directory, $fileName);

        return TaskCommentAttachment::create([
            'task_comment_id' => $taskComment->id,
            'file_name' => $originalName,
            'file_path' => 'uploads/task_comment_attachments/'.$taskComment->id.'/'.$fileName,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    private function extractMentions(string $comment): array
    {
        preg_match_all('/@([A-Za-z0-9_\.\-]+)/', $comment, $matches);

        return collect($matches[1] ?? [])->unique()->values()->all();
    }
}
