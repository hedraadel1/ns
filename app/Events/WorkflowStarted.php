<?php
namespace App\Events;
class WorkflowStarted extends Event
{
    public function __construct(public string $workflowId, public array $context, public array $metadata = [])
    { parent::__construct(); }
}
