<?php

namespace App\Events;

/**
 * MemoryVectorized Event - Broadcast when memory has been vectorized
 *
 * Fired after embedding vector is generated for a memory.
 * Allows UI to track memory processing progress.
 */
class MemoryVectorized extends Event
{
    public function __construct(
        public string $memoryId,
        public int $vectorDimension,
    ) {}
}
