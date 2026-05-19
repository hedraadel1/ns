<?php

namespace App\Events;

/**
 * MessageCompleted Event - Broadcast when LLM message finishes streaming
 *
 * Fired when the complete response is ready.
 * Allows frontend to finalize the message display.
 */
class MessageCompleted extends Event
{
    public function __construct(
        public string $conversationId,
        public string $messageId,
        public string $finalMessage,
    ) {}
}
