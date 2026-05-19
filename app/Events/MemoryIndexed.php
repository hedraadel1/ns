<?php

namespace App\Events;

/**
 * MemoryIndexed Event - Broadcast when memory is indexed in Pinecone
 *
 * Fired after vector is successfully stored in Pinecone.
 * Allows UI to mark memory as fully processed and searchable.
 */
class MemoryIndexed extends Event
{
    public function __construct(
        public string $memoryId,
        public string $pineconeId,
    ) {}
}
