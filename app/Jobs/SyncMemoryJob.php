<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Memory\WorkingMemoryService;
use App\Services\Memory\EpisodicMemoryService;
use App\Services\Memory\SemanticMemoryService;
use App\Services\Memory\StructuredMemoryService;
use App\Services\Memory\GraphMemoryService;

class SyncMemoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contactId;
    protected $memoryType;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $contactId,
        string $memoryType = 'all'
    ) {
        $this->contactId = $contactId;
        $this->memoryType = $memoryType;
    }

    /**
     * Execute the job.
     */
    public function handle(
        WorkingMemoryService $workingMemoryService,
        EpisodicMemoryService $episodicMemoryService,
        SemanticMemoryService $semanticMemoryService,
        StructuredMemoryService $structuredMemoryService,
        GraphMemoryService $graphMemoryService
    ) {
        try {
            Log::info('Starting internal memory sync job', [
                'contactId' => $this->contactId,
                'memoryType' => $this->memoryType
            ]);

            $typesToSync = $this->memoryType === 'all' 
                ? ['working', 'episodic', 'semantic', 'structured', 'graph'] 
                : [$this->memoryType];

            foreach ($typesToSync as $type) {
                switch ($type) {
                    case 'working':
                        $this->syncWorkingMemory($workingMemoryService);
                        break;
                    case 'episodic':
                        $this->syncEpisodicMemory($episodicMemoryService);
                        break;
                    case 'semantic':
                        if ($semanticMemoryService) {
                            $this->syncSemanticMemory($semanticMemoryService);
                        }
                        break;
                    case 'structured':
                        if ($structuredMemoryService) {
                            $this->syncStructuredMemory($structuredMemoryService);
                        }
                        break;
                    case 'graph':
                        if ($graphMemoryService) {
                            $this->syncGraphMemory($graphMemoryService);
                        }
                        break;
                }
            }

            Log::info('Internal memory sync job completed successfully', [
                'contactId' => $this->contactId,
                'memoryType' => $this->memoryType
            ]);
        } catch (\Exception $e) {
            Log::error('Internal memory sync job failed', [
                'contactId' => $this->contactId,
                'memoryType' => $this->memoryType,
                'error' => $e->getMessage()
            ]);

            // Optionally, you could re-throw the exception to trigger retry logic
            // throw $e;
        }
    }

    /**
     * Sync working memory internally.
     */
    protected function syncWorkingMemory(WorkingMemoryService $workingMemoryService)
    {
        Log::info('Performing internal working memory maintenance');
        // Add internal sync/maintenance logic here
    }

    /**
     * Sync episodic memory internally.
     */
    protected function syncEpisodicMemory(EpisodicMemoryService $episodicMemoryService)
    {
        Log::info('Performing internal episodic memory maintenance');
        // Add internal sync/maintenance logic here
    }

    /**
     * Sync semantic memory internally.
     */
    protected function syncSemanticMemory(SemanticMemoryService $semanticMemoryService)
    {
        Log::info('Performing internal semantic memory maintenance');
        // Add internal sync/maintenance logic here
    }

    /**
     * Sync structured memory internally.
     */
    protected function syncStructuredMemory(StructuredMemoryService $structuredMemoryService)
    {
        Log::info('Performing internal structured memory maintenance');
        // Add internal sync/maintenance logic here
    }

    /**
     * Sync graph memory internally.
     */
    protected function syncGraphMemory(GraphMemoryService $graphMemoryService)
    {
        Log::info('Performing internal graph memory maintenance');
        // Add internal sync/maintenance logic here
    }
}