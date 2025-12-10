<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Private conversation channel - for message delivery
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    // Find conversation
    $conversation = Conversation::find($conversationId);

    // Return null if conversation doesn't exist
    if (!$conversation) {
        return null;
    }

    // Check if user is a participant
    $isParticipant = $conversation->hasParticipant($user);

    // Return user data if authorized
    return $isParticipant ? [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url ?? null,
    ] : null;
});
