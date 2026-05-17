<?php
namespace App\Listeners;
use App\Events\ContactCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
class ProcessContactCreated extends Listener implements ShouldQueue
{
    public bool $shouldQueue = true;
    public string $queue = 'contacts';
    public function handle(ContactCreated $event): void
    {
        try {
            $this->log("Processing new contact: {$event->contact->name}");
            $event->contact->memories()->create(['type' => 'working', 'title' => 'Initial Contact Memory', 'content' => json_encode(['name' => $event->contact->name, 'created_at' => now(), 'initial_context' => $event->metadata])]);
        } catch (\Exception $e) { $this->log("Error processing contact creation: " . $e->getMessage(), 'error'); throw $e; }
    }
}
