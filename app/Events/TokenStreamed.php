<?php

namespace App\Events;

/**
 * TokenStreamed Event - Broadcast individual tokens from LLM streaming
 *
 * Fired during token-by-token streaming of LLM responses.
 * Allows frontend to display text as it arrives in real-time.
 */
class TokenStreamed extends Event
{
    public function __construct(
        public string $conversationId,
        public string $messageId,
        public string $token,
    ) {}
}
