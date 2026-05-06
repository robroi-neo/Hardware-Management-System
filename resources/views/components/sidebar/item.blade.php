@props([
    'href' => '#',
    'active' => false,
])

@php
    $baseClasses = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition';
    $activeClasses = 'bg-slate-900 font-medium text-white';
    $inactiveClasses = 'text-slate-700 hover:bg-black/10';
    $classes = $baseClasses . ' ' . ($active ? $activeClasses : $inactiveClasses);
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
