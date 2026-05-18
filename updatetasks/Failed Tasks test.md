# Failed Tasks test

Date: 2026-05-18

## Summary

- Current failing tests captured here for later fixes.

## Failed tests

- `Tests\Feature\TaskCrudTest::test_task_crud_and_transition_actions_work` — Failed asserting that `null` is identical to `1` (the created `AgentTask` was not found after POST).

## Test code (current failing test)

```php
<?php

namespace Tests\Feature;

use App\Models\AgentTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_crud_and_transition_actions_work()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title' => 'Test task',
                'description' => 'A task description',
                'priority' => 5,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Test task')
            ->assertJsonPath('data.status', 'pending');

        $task = AgentTask::first();
        $this->assertNotNull($task);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Updated title',
                'priority' => 8,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.priority', 8);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/tasks/{$task->id}/cancel")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $task->id);

        $this->assertDatabaseHas('agent_tasks', ['id' => $task->id]);
    }
}
```

## Notes / Next steps

- Keep this file updated with failing tests. When all tests are fixed, we will remove or archive this file.
- Next: begin working on the next task (`05_Tasks_CRUD.md`) to address the underlying failure.
