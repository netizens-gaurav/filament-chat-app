<x-filament-panels::page class="h-[calc(100vh-4rem)] flex flex-col">
    {{-- Custom CSS to fix Filament layout constraints for chat --}}
    @push('styles')
        <style>
            /* Force the main container to fill height */
            .fi-main {
                height: 100vh;
                max-height: 100vh;
                padding: 0 !important;
                overflow: hidden;
            }

            .fi-body {
                height: 100%;
                overflow: hidden;
            }

            /* Manually handle height if Tailwind classes fail */
            .chat-layout {
                height: calc(100vh - 4rem);
                /* Adjust 4rem based on your navbar height */
                display: flex;
                flex-direction: column;
            }
        </style>
    @endpush

    <div
        class="flex-1 flex overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 -mx-4 -my-4 md:mx-0 md:my-0">
        {{-- Sidebar (Conversations List) --}}
        <div
            class="flex flex-col w-full md:w-80 lg:w-96 border-r border-gray-200 dark:border-gray-800 {{ $selectedConversationId ? 'hidden md:flex' : 'flex' }}">
            {{-- Sidebar Header --}}
            <div class="p-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900">
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Messages</h2>
            </div>

            {{-- Conversation List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($this->conversations as $conversation)
                    @php
                        $isActive = $selectedConversationId === $conversation->id;
                        $otherUser = $conversation->users->where('id', '!=', auth()->id())->first();
                        $displayName = $conversation->getDisplayTitle(auth()->user());
                        // Fallback initials
                        $initials = substr($displayName, 0, 1);
                    @endphp

                    <button wire:click="selectConversation({{ $conversation->id }})"
                        class="w-full text-left p-4 transition-colors duration-200 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/5
                            {{ $isActive ? 'bg-primary-50 dark:bg-primary-900/10 border-l-4 border-l-primary-500' : 'border-l-4 border-l-transparent' }}">
                        <div class="flex items-start gap-3">
                            {{-- Avatar --}}
                            <div class="relative">
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold text-sm">
                                    {{ $initials }}
                                </div>
                                {{-- Online Indicator (Optional) --}}
                                <span
                                    class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-gray-900 bg-green-400"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                        {{ $displayName }}
                                    </h3>
                                    @if ($conversation->last_message_at)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">
                                            {{ $conversation->last_message_at->shortAbsoluteDiffForHumans() }}
                                        </span>
                                    @endif
                                </div>

                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400 truncate {{ $isActive ? 'text-primary-600 dark:text-primary-400' : '' }}">
                                    @if ($conversation->latestMessage->first())
                                        @if ($conversation->latestMessage->first()->user_id === auth()->id())
                                            <span class="opacity-70">You:</span>
                                        @endif
                                        {{ Str::limit($conversation->latestMessage->first()->content, 35) }}
                                    @else
                                        <span class="italic opacity-70">Start chatting...</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center h-64 text-center px-4">
                        <div
                            class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-3">
                            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-gray-400" />
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">No conversations yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Main Chat Area --}}
        <div
            class="flex-1 flex flex-col h-full min-w-0 bg-white dark:bg-gray-900 {{ !$selectedConversationId ? 'hidden md:flex' : 'flex' }}">
            @if ($this->selectedConversation)
                {{-- Chat Header --}}
                <div
                    class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
                    <div class="flex items-center gap-3">
                        {{-- Mobile Back Button --}}
                        <button wire:click="resetConversation"
                            class="md:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400">
                            <x-heroicon-m-arrow-left class="w-6 h-6" />
                        </button>

                        <div
                            class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold">
                            {{ substr($this->selectedConversation->getDisplayTitle(auth()->user()), 0, 1) }}
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-gray-100">
                                {{ $this->selectedConversation->getDisplayTitle(auth()->user()) }}
                            </h2>
                            <p class="text-xs text-green-500 font-medium">Active now</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2">
                        <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <x-heroicon-o-information-circle class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div class="flex-1 relative overflow-hidden bg-gray-50 dark:bg-gray-950/50">
                    <div class="absolute inset-0">
                        @livewire('chat.message-list', ['conversationId' => $selectedConversationId], key('messages-' . $selectedConversationId))
                    </div>
                </div>

                {{-- Input Area --}}
                <div class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 p-4">
                    @livewire('chat.message-input', ['conversationId' => $selectedConversationId], key('input-' . $selectedConversationId))
                </div>
            @else
                {{-- Empty State --}}
                <div
                    class="flex-1 flex flex-col items-center justify-center text-center p-6 bg-gray-50 dark:bg-gray-950/50">
                    <div
                        class="w-20 h-20 bg-white dark:bg-gray-800 rounded-full shadow-sm flex items-center justify-center mb-4">
                        <x-heroicon-o-paper-airplane class="w-10 h-10 text-primary-500 rotate-90" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Select a Conversation</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm">
                        Choose a person from the list on the left to start chatting or view your history.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
