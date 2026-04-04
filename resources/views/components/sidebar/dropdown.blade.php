@props([
    'label',
    'open' => false,
])

<div x-data="{ open: @js($open) }" class="space-y-1">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-indigo-100 transition hover:bg-white/10"
    >
        @isset($icon)
            {{ $icon }}
        @endisset

        <span class="flex-1 text-left">{{ $label }}</span>

        <svg
            class="h-3 w-3 transition-transform duration-150"
            :class="open ? 'rotate-180' : ''"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition.origin-top
        x-cloak
        class="space-y-1 border-l border-indigo-400/40 pl-6"
    >
        {{ $slot }}
    </div>
</div>
