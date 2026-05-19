<?php

namespace App\Events;

/**
 * WorkflowStepCompleted
 *
 * Dispatched when a single step within a workflow completes.
 */
class WorkflowStepCompleted extends Event
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $workflowId,
        public string $stepId,
        public string $stepTitle,
        public array $result = [],
        public array $metadata = []
    ) {
        parent::__construct();
    }
}
