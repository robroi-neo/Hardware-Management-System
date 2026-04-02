@props([
    'href' => '#',
    'active' => false,
])

@php
    $baseClasses = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition';
    $activeClasses = 'bg-indigo-500/90 font-medium text-white';
    $inactiveClasses = 'text-indigo-100 hover:bg-white/10';
    $classes = $baseClasses . ' ' . ($active ? $activeClasses : $inactiveClasses);
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
    
    {{-- Example usage (in sidebar):
    <x-sidebar-link
        href="{{ route('dashboard') }}"
        :active="request()->routeIs('dashboard')"
        @click="mobileOpen = false"
    >
        <svg>...</svg>
        <span>Dashboard</span>
    </x-sidebar-link>
    --}}
</a>
