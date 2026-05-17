<?php

namespace App\Services;

use App\Models\AgentTask;
use App\Models\Workflow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TaskQueueService
{
    protected array $queue = [];
    protected array $processing = [];
    protected array $completed = [];
    protected array $failed = [];

    public function enqueue(AgentTask $task, array $options = []): AgentTask
    {
        $task->update([
            'status' => 'pending',
            'metadata' => array_merge($task->metadata ?? [], [
                'queued_at' => now()->toISOString(),
                'queue_options' => $options,
            ]),
        ]);

        $this->queue[] = $task->id;
        Log::info("Task enqueued: {$task->title} (ID: {$task->id})");

        return $task;
    }

    public function dequeue(): ?AgentTask
    {
        if (empty($this->queue)) {
            return null;
        }

        $taskId = array_shift($this->queue);
        $task = AgentTask::find($taskId);

        if ($task) {
            $task->update(['status' => 'running']);
            $this->processing[] = $taskId;
            Log::info("Task dequeued: {$task->title} (ID: {$task->id})");
        }

        return $task;
    }

    public function complete(AgentTask $task, array $result = []): AgentTask
    {
        $task->update([
            'status' => 'completed',
            'progress' => 100,
            'metadata' => array_merge($task->metadata ?? [], [
                'completed_at' => now()->toISOString(),
                'result' => $result,
            ]),
        ]);

        $this->processing = array_filter($this->processing, fn($id) => $id !== $task->id);
        $this->completed[] = $task->id;

        Log::info("Task completed: {$task->title} (ID: {$task->id})");
        return $task;
    }

    public function fail(AgentTask $task, string $error = null): AgentTask
    {
        $task->update([
            'status' => 'failed',
            'metadata' => array_merge($task->metadata ?? [], [
                'failed_at' => now()->toISOString(),
                'error' => $error,
            ]),
        ]);

        $this->processing = array_filter($this->processing, fn($id) => $id !== $task->id);
        $this->failed[] = $task->id;

        Log::error("Task failed: {$task->title} (ID: {$task->id})", [
            'error' => $error,
        ]);

        return $task;
    }

    public function cancel(AgentTask $task): AgentTask
    {
        $task->update(['status' => 'cancelled']);

        $this->queue = array_filter($this->queue, fn($id) => $id !== $task->id);
        $this->processing = array_filter($this->processing, fn($id) => $id !== $task->id);

        Log::info("Task cancelled: {$task->title} (ID: {$task->id})");
        return $task;
    }

    public function pause(AgentTask $task): AgentTask
    {
        $task->update(['status' => 'paused']);
        Log::info("Task paused: {$task->title} (ID: {$task->id})");
        return $task;
    }

    public function resume(AgentTask $task): AgentTask
    {
        $task->update(['status' => 'pending']);
        $this->enqueue($task);
        Log::info("Task resumed: {$task->title} (ID: {$task->id})");
        return $task;
    }

    public function getQueueSize(): int
    {
        return count($this->queue);
    }

    public function getProcessingSize(): int
    {
        return count($this->processing);
    }

    public function getCompletedCount(): int
    {
        return count($this->completed);
    }

    public function getFailedCount(): int
    {
        return count($this->failed);
    }

    public function getStats(): array
    {
        return [
            'queued' => $this->getQueueSize(),
            'processing' => $this->getProcessingSize(),
            'completed' => $this->getCompletedCount(),
            'failed' => $this->getFailedCount(),
            'total' => $this->getQueueSize() + $this->getProcessingSize() + $this->getCompletedCount() + $this->getFailedCount(),
        ];
    }

    public function clearQueue(): void
    {
        $this->queue = [];
        Log::info('Task queue cleared');
    }

    public function clearCompleted(): void
    {
        $this->completed = [];
        Log::info('Completed tasks cleared');
    }

    public function clearFailed(): void
    {
        $this->failed = [];
        Log::info('Failed tasks cleared');
    }

    public function getQueuedTaskIds(): array
    {
        return $this->queue;
    }

    public function getProcessingTaskIds(): array
    {
        return $this->processing;
    }
}
