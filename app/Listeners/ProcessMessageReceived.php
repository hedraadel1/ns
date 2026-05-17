<?php
namespace App\Listeners;
use App\Events\MessageReceived;
use App\Events\MemoryIndexed;
use Illuminate\Contracts\Queue\ShouldQueue;
class ProcessMessageReceived extends Listener implements ShouldQueue
{
    public bool $shouldQueue = true;
    public string $queue = 'messages';
    public function handle(MessageReceived $event): void
    {
        try {
            $this->log("Processing message from {$event->contact->name}");
            event(new MemoryIndexed($event->contact, 'episodic', ['type' => 'message_received', 'message' => $event->message, 'timestamp' => now()], $event->metadata));
        } catch (\Exception $e) { $this->log("Error processing message: " . $e->getMessage(), 'error'); throw $e; }
    }
}
