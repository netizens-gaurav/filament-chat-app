<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;


class MessageList extends Component
{
    public int $conversationId;

    public $messages = [];

    public function mount(): void
    {
        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        $conversation = Conversation::find($this->conversationId);

        if (!$conversation || !$conversation->hasParticipant(Auth::user())) {
            $this->messages = [];
            return;
        }

        $this->messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->toArray();
    }

    #[On('echo-private:conversation.{conversationId},message.sent')]
    public function handleNewMessage($event): void
    {
        // Add new message to the list
        $this->messages[] = $event;

        // Dispatch browser event to scroll to bottom
        $this->dispatch('messageAdded');
    }

    public function render()
    {
        return view('livewire.chat.message-list');
    }
}
