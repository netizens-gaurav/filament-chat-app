<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use Livewire\Component;
use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class MessageInput extends Component
{
    public int $conversationId;

    public string $content = '';

    public function sendMessage(): void
    {
        $user = Auth::user();

        // Rate limiting: 30 messages per minute
        $executed = RateLimiter::attempt(
            'send-message:' . $user->id,
            30,
            function () {
                $this->performSend();
            },
            60
        );

        if (!$executed) {
            $this->addError('content', 'Too many messages. Please slow down.');
            return;
        }
    }

    protected function performSend(): void
    {
        $this->validate([
            'content' => 'required|string|max:5000',
        ]);

        $conversation = Conversation::find($this->conversationId);

        $user = Auth::user();

        // Verify user has access
        if (!$conversation || !$conversation->hasParticipant($user)) {
            $this->addError('content', 'You do not have access to this conversation.');
            return;
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $this->conversationId,
            'user_id' => $user->id,
            'content' => $this->content,
            'type' => 'text',
        ]);

        // Load relationship for broadcasting
        $message->load('user');

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        // Clear input
        $this->content = '';

        // Dispatch event to add message locally (for sender)
        $this->dispatch('messageAdded');
    }

    public function render()
    {
        return view('livewire.chat.message-input');
    }
}
