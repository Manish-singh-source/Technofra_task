<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_todo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('todos.store'), [
            'title' => 'Daily follow up',
            'description' => 'Client ko call karna hai',
            'task_date' => '2026-03-14',
            'task_time' => '10:00',
            'repeat_interval' => 1,
            'repeat_unit' => 'week',
            'repeat_days' => ['monday', 'friday'],
            'reminder_time' => '09:45',
            'starts_on' => '2026-03-14',
            'ends_type' => 'after',
            'ends_after_occurrences' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Daily follow up');

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'Daily follow up',
            'repeat_unit' => 'week',
            'ends_type' => 'after',
        ]);
    }

    public function test_user_only_sees_their_own_todos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Todo::create([
            'user_id' => $user->id,
            'title' => 'My Todo',
            'task_date' => '2026-03-14',
            'repeat_interval' => 1,
            'repeat_unit' => 'day',
            'starts_on' => '2026-03-14',
            'ends_type' => 'never',
        ]);

        Todo::create([
            'user_id' => $otherUser->id,
            'title' => 'Other Todo',
            'task_date' => '2026-03-14',
            'repeat_interval' => 1,
            'repeat_unit' => 'day',
            'starts_on' => '2026-03-14',
            'ends_type' => 'never',
        ]);

        $response = $this->actingAs($user)->getJson(route('todos.list'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'My Todo');
    }
}
