<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\AgentTask;
use App\Services\AgentRegistry;
use App\Services\TaskRoutingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class WorkflowExecutor
{
    protected AgentRegistry $registry;
    protected TaskRoutingService $router;
    protected WorkflowValidationService $validator;
    protected WorkflowErrorHandler $errorHandler;
    protected array $executionContext = [];
    protected array $stepResults = [];

    public function __construct(
        AgentRegistry $registry,
        TaskRoutingService $router,
        WorkflowValidationService $validator,
        WorkflowErrorHandler $errorHandler
    ) {
        $this->registry = $registry;
        $this->router = $router;
        $this->validator = $validator;
        $this->errorHandler = $errorHandler;
    }

    public function execute(Workflow $workflow, array $context = []): array
    {
        $this->executionContext = $context;
        $this->stepResults = [];

        if (!$workflow->canExecute()) {
            throw new \InvalidArgumentException(
                "Workflow cannot be executed in status: {$workflow->status}"
            );
        }

        $workflow->setRunning();
        $workflow->incrementExecution();
        Log::info("Workflow execution started: {$workflow->name} (ID: {$workflow->id})");

        $steps = $workflow->steps ?? [];
        $totalSteps = count($steps);
        $completedSteps = 0;
        $failedSteps = 0;

        foreach ($steps as $index => $step) {
            $stepOrder = $step['step_order'] ?? $step['order'] ?? $index;
            $stepName = $step['name'] ?? $step['title'] ?? "Step {$stepOrder}";

            try {
                $validation = $this->validator->validateStep($step, $this->executionContext);
                if (!$validation['valid']) {
                    throw new \InvalidArgumentException(
                        "Step validation failed: " . implode(', ', $validation['errors'])
                    );
                }

                $stepResult = $this->executeStep($workflow, $step, $stepOrder, $context);
                $this->stepResults[] = $stepResult;

                if ($stepResult['success']) {
                    $completedSteps++;
                    $this->executionContext = array_merge(
                        $this->executionContext,
                        $stepResult['output'] ?? []
                    );
                } else {
                    $failedSteps++;
                    $errorResult = $this->errorHandler->handleStepFailure(
                        $workflow, $step, $stepResult, $this->stepResults
                    );

                    if ($errorResult['should_retry']) {
                        $retryResult = $this->retryStep($workflow, $step, $stepOrder, $context, $errorResult);
                        if ($retryResult['success']) {
                            $completedSteps++;
                            $failedSteps--;
                            $this->stepResults[] = $retryResult;
                        }
                    }

                    if ($errorResult['should_abort']) {
                        Log::warning("Workflow aborted at step {$stepOrder}: {$stepName}");
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $failedSteps++;
                Log::error("Workflow step failed: {$stepName}", [
                    'error' => $e->getMessage(),
                    'step_order' => $stepOrder,
                ]);

                $this->stepResults[] = [
                    'step' => $stepName,
                    'step_order' => $stepOrder,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $allStepsCompleted = $completedSteps === $totalSteps;
        $hasFailures = $failedSteps > 0;

        if ($allStepsCompleted) {
            $workflow->recordSuccess();
            Log::info("Workflow completed successfully: {$workflow->name}");
        } elseif ($hasFailures) {
            $workflow->recordError();
            Log::error("Workflow failed: {$workflow->name}", [
                'completed' => $completedSteps,
                'failed' => $failedSteps,
                'total' => $totalSteps,
            ]);
        } else {
            $workflow->update(['status' => Workflow::STATUS_PAUSED]);
        }

        return [
            'success' => $allStepsCompleted,
            'workflow_id' => $workflow->id,
            'workflow_name' => $workflow->name,
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'failed_steps' => $failedSteps,
            'progress' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100, 2) : 0,
            'step_results' => $this->stepResults,
            'execution_context' => $this->executionContext,
        ];
    }

    protected function executeStep(Workflow $workflow, array $step, int $stepOrder, array $context): array
    {
        $stepName = $step['name'] ?? $step['title'] ?? "Step {$stepOrder}";
        $agentType = $step['agent_type'] ?? null;
        $task = $step['task'] ?? $stepName;
        $stepContext = array_merge($context, $step['context'] ?? []);

        $startTime = microtime(true);

        try {
            if ($agentType) {
                $agent = \App\Models\Agent::where('type', $agentType)->first();
                if (!$agent) {
                    throw new \InvalidArgumentException("Agent not found for type: {$agentType}");
                }

                $instance = $this->registry->resolve($agent);
                $result = $instance->execute(array_merge($stepContext, ['task' => $task]));
            } else {
                $result = $this->executeGenericStep($step, $stepContext);
            }

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("Workflow step completed: {$stepName}", [
                'duration_ms' => $durationMs,
            ]);

            return [
                'step' => $stepName,
                'step_order' => $stepOrder,
                'success' => $result['success'] ?? false,
                'result' => $result,
                'duration_ms' => $durationMs,
            ];
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'step' => $stepName,
                'step_order' => $stepOrder,
                'success' => false,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ];
        }
    }

    protected function executeGenericStep(array $step, array $context): array
    {
        $action = $step['action'] ?? 'process';

        return match ($action) {
            'delay' => [
                'success' => true,
                'action' => 'delayed',
                'duration' => $step['duration'] ?? 1,
            ],
            'log' => [
                'success' => true,
                'action' => 'logged',
                'message' => $step['message'] ?? '',
            ],
            'condition' => [
                'success' => true,
                'action' => 'condition_evaluated',
                'result' => $this->evaluateCondition($step['condition'] ?? [], $context),
            ],
            default => [
                'success' => true,
                'action' => 'processed',
                'output' => 'Processed step: ' . ($step['name'] ?? 'unknown'),
            ],
        };
    }

    protected function evaluateCondition(array $condition, array $context): bool
    {
        if (empty($condition)) return true;

        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '==';
        $value = $condition['value'] ?? null;

        if ($field === null) return false;

        $contextValue = Arr::get($context, $field);

        return match ($operator) {
            '==' => $contextValue == $value,
            '===' => $contextValue === $value,
            '!=' => $contextValue != $value,
            '!==' => $contextValue !== $value,
            '>' => $contextValue > $value,
            '<' => $contextValue < $value,
            '>=' => $contextValue >= $value,
            '<=' => $contextValue <= $value,
            'contains' => is_string($contextValue) && str_contains($contextValue, $value),
            'in' => in_array($contextValue, (array) $value),
            default => false,
        };
    }

    protected function retryStep(Workflow $workflow, array $step, int $stepOrder, array $context, array $errorResult): array
    {
        $maxRetries = $step['max_retries'] ?? $workflow->settings['max_retries'] ?? 3;
        $retryDelay = $step['retry_delay'] ?? $workflow->settings['retry_delay'] ?? 60;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            Log::info("Retrying step {$stepOrder}, attempt {$attempt}/{$maxRetries}");

            sleep(min($retryDelay * $attempt, 300));

            $result = $this->executeStep($workflow, $step, $stepOrder, $context);
            if ($result['success']) {
                Log::info("Step retry succeeded on attempt {$attempt}");
                return $result;
            }
        }

        Log::error("Step retry exhausted after {$maxRetries} attempts");
        return [
            'step' => $step['name'] ?? "Step {$stepOrder}",
            'step_order' => $stepOrder,
            'success' => false,
            'error' => "Max retries ({$maxRetries}) exceeded",
            'retries_exhausted' => true,
        ];
    }

    public function getStepResults(): array
    {
        return $this->stepResults;
    }

    public function getExecutionContext(): array
    {
        return $this->executionContext;
    }
}
