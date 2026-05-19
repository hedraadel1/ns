<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The required callback is used to determine if an
| authenticated user can listen to the channel.
|
*/

/**
 * Private channel for user-specific messages
 *
 * Only the authenticated user can listen to their own messages
 */
Broadcast::channel('user.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

/**
 * Private channel for conversation-specific events
 *
 * Users can listen to conversations they're part of
 * Authorization checks if user has access to this conversation
 */
Broadcast::channel('conversation.{conversationId}', function (User $user, string $conversationId) {
    try {
        $conversation = Conversation::findOrFail($conversationId);
        
        // User must be the conversation owner or have been granted access
        return $user->id === $conversation->user_id || 
               $user->conversations()->where('id', $conversationId)->exists();
    } catch (Exception $e) {
        return false;
    }
});

/**
 * Private channel for message streaming events
 *
 * Users can listen to messages within conversations they have access to
 */
Broadcast::channel('message.{messageId}', function (User $user, string $messageId) {
    try {
        $message = \App\Models\Message::with('conversation')->findOrFail($messageId);
        
        // Check if user has access to the parent conversation
        return $user->id === $message->conversation->user_id || 
               $user->conversations()->where('id', $message->conversation_id)->exists();
    } catch (Exception $e) {
        return false;
    }
});

/**
 * Private channel for session-specific events
 *
 * Each session has its own private channel for live updates
 */
Broadcast::channel('session.{sessionId}', function (User $user, string $sessionId) {
    try {
        // Session should belong to this user
        // This can be stored in cache or session store
        $sessionUser = cache("session:{$sessionId}:user");
        
        if (!$sessionUser) {
            // Fallback: check if we can find it in the request
            return true; // Allow for now, refine based on actual session storage
        }
        
        return $user->id === $sessionUser;
    } catch (Exception $e) {
        return false;
    }
});

/**
 * Private channel for contact-specific activity
 *
 * Users can listen to activity related to their contacts
 */
Broadcast::channel('contact.{contactId}', function (User $user, string $contactId) {
    try {
        $contact = \App\Models\Contact::findOrFail($contactId);
        
        // User must be the contact owner
        return $user->id === $contact->user_id;
    } catch (Exception $e) {
        return false;
    }
});

/**
 * Private channel for job/batch progress updates
 *
 * Users can monitor their own job batches
 */
Broadcast::channel('batch.{batchId}', function (User $user, string $batchId) {
    try {
        // Jobs should track which user initiated them
        $batch = \App\Models\JobBatch::findOrFail($batchId);
        
        return $user->id === $batch->user_id;
    } catch (Exception $e) {
        return false;
    }
});

/**
 * Private channel for real-time memory updates
 *
 * Users can listen to memory indexing and vectorization events
 */
Broadcast::channel('memory.{memoryId}', function (User $user, string $memoryId) {
    try {
        $memory = \App\Models\Memory::findOrFail($memoryId);
        
        // Check if memory belongs to user's contacts or conversations
        return $user->memories()->where('id', $memoryId)->exists() ||
               $user->contacts()
                   ->whereHas('memories', fn($q) => $q->where('id', $memoryId))
                   ->exists();
    } catch (Exception $e) {
        return false;
    }
});

/**
 * Admin channel for system-wide monitoring
 *
 * Only admins can listen to system events
 */
Broadcast::channel('admin.system', function (User $user) {
    return $user->hasRole('admin') || $user->is_admin === true;
});

/**
 * Admin channel for job monitoring
 *
 * Only admins can see all job statuses
 */
Broadcast::channel('admin.jobs', function (User $user) {
    return $user->hasRole('admin') || $user->is_admin === true;
});

/**
 * Channel for connection status
 *
 * All authenticated users can listen to their own connection status
 */
Broadcast::channel('connection.{userId}', function (User $user, int $userId) {
    return (int) $user->id === $userId;
});
