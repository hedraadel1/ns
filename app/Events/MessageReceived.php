<?php
namespace App\Events;
use App\Models\Contact;
use App\Models\Conversation;
class MessageReceived extends Event
{
    public function __construct(public Contact $contact, public Conversation $conversation, public string $message, public array $metadata = [])
    { parent::__construct(); }
}
