<!-- resources/views/livewire/toast-notification.blade.php -->

<div>
    @if($show)
        <div x-data="{ show: @entangle('show') }"
             x-init="() => {
                 $watch('show', value => {
                     if (value) {
                         setTimeout(() => { show = false }, {{ $duration }});
                     }
                 });
             }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-4 right-4 z-50">
            <div @class([
                'px-4 py-3 rounded shadow-lg',
                'bg-red-100 border-l-4 border-red-500 text-red-700' => $type === 'error',
                'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700' => $type === 'warning',
                'bg-green-100 border-l-4 border-green-500 text-green-700' => $type === 'success',
            ])>
                <div class="flex items-center">
                    @if($type === 'error')
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($type === 'warning')
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @endif
                    <span>{{ $message }}</span>
                    <button @click="show = false" class="ml-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>