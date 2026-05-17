<?php
namespace App\Events;
use App\Models\Contact;
class MemoryIndexed extends Event
{
    public function __construct(public Contact $contact, public string $memoryType, public array $content, public array $metadata = [])
    { parent::__construct(); }
}
