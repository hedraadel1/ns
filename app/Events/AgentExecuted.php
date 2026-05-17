<?php
namespace App\Events;
class AgentExecuted extends Event
{
    public function __construct(public string $agentId, public array $input, public array $output, public array $metadata = [])
    { parent::__construct(); }
}
