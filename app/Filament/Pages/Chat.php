<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class Chat extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-oval-left';

    protected static ?string $navigationLabel = 'Chat';

    protected static ?string $title = '';

    protected string $view = 'filament.pages.chat';

    protected static ?string $maxWidth = 'full';

    public ?int $selectedConversationId = null;

    public function mount(): void
    {
        // Auto-select first conversation if available
        $firstConversation = Auth::user()->conversations()->latest('last_message_at')->first();

        if ($firstConversation) {
            $this->selectedConversationId = $firstConversation->id;
        }
    }

    public function selectConversation(int $conversationId): void
    {
        $conversation = Conversation::find($conversationId);

        // Verify user has access
        if (!$conversation || !$conversation->hasParticipant(Auth::user())) {
            return;
        }

        $this->selectedConversationId = $conversationId;

        // Mark conversation as read
        $conversation->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now(),
        ]);
    }

    public function resetConversation(): void
    {
        $this->selectedConversationId = null;
    }

    public function getConversationsProperty()
    {
        return Auth::user()
            ->conversations()
            ->with(['latestMessage.user', 'users'])
            ->latest('last_message_at')
            ->get();
    }

    public function getSelectedConversationProperty()
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        return Conversation::with('users')->find($this->selectedConversationId);
    }
}
