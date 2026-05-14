@php
    $successMessage = session('success');
    $errorMessage = session('error');
    $message = $successMessage ?? $errorMessage;
    $type = $successMessage ? 'success' : ($errorMessage ? 'error' : null);
@endphp

@if ($message)
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed top-5 right-5 z-50 w-full max-w-sm"
    >
        <div class="flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl shadow-slate-900/10
            {{ $type === 'success' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-red-600 border-red-500 text-white' }}">
            <div class="mt-0.5 flex-shrink-0">
                @if ($type === 'success')
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>

            <div class="min-w-0 flex-1 text-sm leading-6">
                <p class="font-semibold">
                    {{ $type === 'success' ? 'Success' : 'Error' }}
                </p>
                <p class="mt-1 text-sm">
                    {{ $message }}
                </p>
            </div>

            <button
                type="button"
                @click="show = false"
                class="rounded-full p-1 text-white/80 transition hover:bg-white/20 hover:text-white"
                aria-label="Close notification"
            >
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6.707 5.293a1 1 0 00-1.414 1.414L8.586 10l-3.293 3.293a1 1 0 001.414 1.414L10 11.414l3.293 3.293a1 1 0 001.414-1.414L11.414 10l3.293-3.293a1 1 0 00-1.414-1.414L10 8.586 6.707 5.293z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
@endif
