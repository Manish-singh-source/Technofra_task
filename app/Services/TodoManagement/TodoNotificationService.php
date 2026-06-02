<?php

namespace App\Services\TodoManagement;

use App\Mail\TodoCrudMail;
use App\Models\Todo;
use App\Models\User;
use App\Notifications\InAppPushNotification;
use App\Services\UnifiedNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TodoNotificationService
{
    public function __construct(
        private readonly UnifiedNotificationService $unifiedNotificationService,
        private readonly WhatsAppService $whatsAppService
    ) {}

    public function notifyCrudOperation(User $recipient, Todo $todo, string $action, array $context = []): void
    {
        $actionLabel = $this->actionLabel($action);
        $subject = sprintf('Todo %s: %s', $actionLabel, $todo->title);
        $message = $this->buildMessage($todo, $actionLabel, $context);

        $payload = [
            'todo_id' => $todo->id,
            'todo_title' => $todo->title,
            'action' => $action,
            'action_label' => $actionLabel,
            'status' => $todo->is_completed ? 'completed' : 'open',
            'context' => $context,
            'todo_url' => route('to-do-list'),
        ];

        $this->sendMail($recipient, $todo, $actionLabel, $message, $subject, $payload);
        $this->sendWhatsApp($recipient, $message);
        $this->sendDatabaseNotification($recipient, $subject, $message, $payload);
        $this->sendAppNotification($recipient, $subject, $message, $payload);
    }

    private function sendMail(User $recipient, Todo $todo, string $actionLabel, string $message, string $subject, array $payload): void
    {
        if (blank($recipient->email)) {
            return;
        }

        try {
            Mail::to($recipient->email)->send(new TodoCrudMail($todo, $recipient, $actionLabel, $message, $subject, $payload));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send todo CRUD mail', [
                'todo_id' => $todo->id,
                'user_id' => $recipient->id,
                'action' => $actionLabel,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendWhatsApp(User $recipient, string $message): void
    {
        $phone = trim((string) ($recipient->phone ?? ''));
        if ($phone === '') {
            return;
        }

        try {
            $this->whatsAppService->sendMessage($phone, $message);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send todo CRUD WhatsApp message', [
                'user_id' => $recipient->id,
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendDatabaseNotification(User $recipient, string $subject, string $message, array $payload): void
    {
        try {
            $recipient->notify(new InAppPushNotification(
                $subject,
                $message,
                'todo_crud',
                $payload
            ));
        } catch (\Throwable $exception) {
            Log::warning('Failed to store todo CRUD database notification', [
                'user_id' => $recipient->id,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendAppNotification(User $recipient, string $subject, string $message, array $payload): void
    {
        try {
            $this->unifiedNotificationService->sendPushOnlyToUser($recipient, $subject, $message, $payload);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send todo CRUD app notification', [
                'user_id' => $recipient->id,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function buildMessage(Todo $todo, string $actionLabel, array $context): string
    {
        $details = match ($context['status'] ?? null) {
            'completed' => 'The todo was marked as completed.',
            'open' => 'The todo was reopened.',
            default => 'The todo record was updated.',
        };

        return trim($actionLabel . ': ' . $todo->title . '. ' . $details);
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'status_changed' => 'Status Changed',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }
}
