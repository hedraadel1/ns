<?php
namespace App\Events;
class WorkflowCompleted extends Event
{
    public function __construct(public string $workflowId, public array $result, public array $metadata = [])
    { parent::__construct(); }
}
