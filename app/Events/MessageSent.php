<?php
namespace App\Events;
use App\Models\Conversation;
class MessageSent extends Event
{
    public function __construct(public Conversation $conversation, public string $message, public string $sender = 'system', public array $metadata = [])
    { parent::__construct(); }
}
