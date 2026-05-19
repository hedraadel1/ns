<?php

namespace App\Services;

use App\Models\Agent;
use App\Services\LogService;
use Illuminate\Support\Str;

class AgentLifecycleService
{
    protected array $stateTransitions = [
        Agent::STATUS_IDLE => [Agent::STATUS_RUNNING],
        Agent::STATUS_RUNNING => [Agent::STATUS_IDLE, Agent::STATUS_PAUSED, Agent::STATUS_ERROR, Agent::STATUS_COMPLETED],
        Agent::STATUS_PAUSED => [Agent::STATUS_RUNNING, Agent::STATUS_IDLE],
        Agent::STATUS_ERROR => [Agent::STATUS_IDLE, Agent::STATUS_RUNNING],
        Agent::STATUS_COMPLETED => [Agent::STATUS_IDLE],
    ];

    public function __construct(protected LogService $logService) {}

    public function initialize(Agent $agent): Agent
    {
        $agent->status = Agent::STATUS_RUNNING;
        $agent->execution_count += 1;
        $agent->last_executed_at = now();
        $agent->save();

        $this->logService->info("Agent initialized: {$agent->name} (ID: {$agent->id})", [
            'channel' => 'agent',
            'type' => 'lifecycle',
            'related_id' => $agent->id,
            'related_type' => Agent::class,
        ]);

        return $agent;
    }

    public function transition(Agent $agent, string $newStatus): Agent
    {
        $currentStatus = $agent->status;

        if (!isset($this->stateTransitions[$currentStatus])) {
            throw new \InvalidArgumentException("Invalid current status: {$currentStatus}");
        }

        if (!in_array($newStatus, $this->stateTransitions[$currentStatus])) {
            throw new \InvalidArgumentException(
                "Invalid state transition from {$currentStatus} to {$newStatus}"
            );
        }

        $agent->update(['status' => $newStatus]);

        $this->logService->info("Agent state transition: {$agent->name} from {$currentStatus} to {$newStatus}", [
            'channel' => 'agent',
            'type' => 'lifecycle',
            'related_id' => $agent->id,
            'related_type' => Agent::class,
            'context' => ['from' => $currentStatus, 'to' => $newStatus],
        ]);

        return $agent->fresh();
    }

    public function idle(Agent $agent): Agent
    {
        return $this->transition($agent, Agent::STATUS_IDLE);
    }

    public function pause(Agent $agent): Agent
    {
        return $this->transition($agent, Agent::STATUS_PAUSED);
    }

    public function resume(Agent $agent): Agent
    {
        return $this->transition($agent, Agent::STATUS_RUNNING);
    }

    public function complete(Agent $agent): Agent
    {
        $agent->recordSuccess();

        $this->logService->info("Agent completed successfully: {$agent->name}", [
            'channel' => 'agent',
            'type' => 'lifecycle',
            'related_id' => $agent->id,
            'related_type' => Agent::class,
        ]);

        return $agent->fresh();
    }

    public function fail(Agent $agent, string $errorMessage = null): Agent
    {
        $agent->recordError();

        if ($errorMessage) {
            $this->logService->error("Agent failed: {$agent->name} - {$errorMessage}", [
                'channel' => 'agent',
                'type' => 'lifecycle',
                'related_id' => $agent->id,
                'related_type' => Agent::class,
                'context' => ['error' => $errorMessage],
            ]);
        }

        return $agent->fresh();
    }

    public function canTransition(Agent $agent, string $newStatus): bool
    {
        $currentStatus = $agent->status;
        return isset($this->stateTransitions[$currentStatus]) &&
               in_array($newStatus, $this->stateTransitions[$currentStatus]);
    }

    public function getAvailableTransitions(Agent $agent): array
    {
        return $this->stateTransitions[$agent->status] ?? [];
    }

    public function getLifecycleState(): array
    {
        return $this->stateTransitions;
    }
}