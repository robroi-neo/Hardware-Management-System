@props([
    'label',
    'open' => false,
])

<div x-data="{ open: @js($open) }" class="space-y-1">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-base text-white transition hover:bg-indigo-600/50"
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
            stroke-width="3"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition.origin-top
        x-cloak
        class="space-y-1 border-l border-slate-300 ml-4 pl-3"
    >
        {{ $slot }}
    </div>
</div>
