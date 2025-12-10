<form wire:submit.prevent="sendMessage" class="relative">
    <div class="flex items-end gap-2 bg-gray-100 dark:bg-gray-800 p-2 rounded-2xl">
        {{-- Attachment Button (Visual Only) --}}
        <button type="button" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
            <x-heroicon-o-paper-clip class="w-5 h-5" />
        </button>

        {{-- Input Field --}}
        <div class="flex-1">
            <textarea
                wire:model="content"
                rows="1"
                placeholder="Type your message..."
                class="w-full bg-transparent border-0 focus:ring-0 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 resize-none py-3 max-h-32"
                style="min-height: 44px;"
                x-data="{ resize() { $el.style.height = '44px'; $el.style.height = $el.scrollHeight + 'px' } }"
                x-init="resize()"
                @input="resize()"
                @keydown.enter.prevent="if(!$event.shiftKey) { $wire.sendMessage(); $el.style.height = '44px'; }"
            ></textarea>
        </div>

        {{-- Send Button --}}
        <button
            type="submit"
            class="p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed mb-1"
            wire:loading.attr="disabled"
            wire:target="sendMessage"
        >
            <x-heroicon-m-paper-airplane class="w-5 h-5 -ml-0.5" />
        </button>
    </div>

    @error('content')
        <div class="absolute -top-6 left-0 text-xs text-red-500 bg-white dark:bg-gray-800 px-2 py-1 rounded shadow">
            {{ $message }}
        </div>
    @enderror
</form>
