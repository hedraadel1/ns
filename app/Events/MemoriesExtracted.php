<?php

namespace App\Events;

/**
 * MemoriesExtracted Event - Broadcast when memories extracted from conversation
 *
 * Fired after key memories/facts are extracted and stored.
 * Allows UI to track memory extraction progress.
 */
class MemoriesExtracted extends Event
{
    public function __construct(
        public string $conversationId,
        public int $extractedCount,
    ) {}
}
