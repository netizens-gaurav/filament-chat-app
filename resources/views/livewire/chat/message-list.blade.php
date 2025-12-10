<div
    x-data="{
        scrollToBottom() {
            this.$nextTick(() => {
                this.$refs.container.scrollTop = this.$refs.container.scrollHeight;
            });
        }
    }"
    x-init="scrollToBottom()"
    @message-added.window="scrollToBottom()"
    class="h-full flex flex-col"
>
    <div x-ref="container" class="flex-1 overflow-y-auto p-4 space-y-6 scroll-smooth">
        @php
            $previousDate = null;
        @endphp

        @forelse($messages as $message)
            @php
                $isMe = $message['user_id'] === auth()->id();
                $messageDate = \Carbon\Carbon::parse($message['created_at']);
                $showDate = $previousDate !== $messageDate->format('Y-m-d');
                $previousDate = $messageDate->format('Y-m-d');
            @endphp

            {{-- Date Divider --}}
            @if($showDate)
                <div class="flex justify-center my-4">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">
                        {{ $messageDate->isToday() ? 'Today' : ($messageDate->isYesterday() ? 'Yesterday' : $messageDate->format('M j, Y')) }}
                    </span>
                </div>
            @endif

            <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} group">
                <div class="flex max-w-[80%] md:max-w-[70%] gap-2 {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">

                    {{-- Avatar --}}
                    <div class="flex-shrink-0 mt-auto">
                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300">
                            {{ substr($message['user']['name'], 0, 1) }}
                        </div>
                    </div>

                    {{-- Message Bubble --}}
                    <div>
                        <div class="
                            px-4 py-2 shadow-sm relative text-sm leading-relaxed
                            {{ $isMe
                                ? 'bg-primary-600 text-white rounded-2xl rounded-tr-sm'
                                : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-2xl rounded-tl-sm border border-gray-100 dark:border-gray-700'
                            }}
                        ">
                            <p class="whitespace-pre-wrap">{{ $message['content'] }}</p>
                        </div>

                        {{-- Timestamp --}}
                        <div class="mt-1 text-[10px] text-gray-400 {{ $isMe ? 'text-right' : 'text-left' }}">
                            {{ \Carbon\Carbon::parse($message['created_at'])->format('g:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                <p>No messages yet.</p>
                <p class="text-sm">Say hello! 👋</p>
            </div>
        @endforelse
    </div>
</div>
