@props([
    'href' => '#',
    'active' => false,
])

@php
    $baseClasses = 'flex items-center gap-3 rounded-md px-3 py-2 text-base transition';
    $activeClasses = 'bg-indigo-600 text-white';
    $inactiveClasses = 'text-white hover:bg-indigo-600/50';
    $classes = $baseClasses . ' ' . ($active ? $activeClasses : $inactiveClasses);
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
