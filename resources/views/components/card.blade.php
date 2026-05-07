@props(['title' => null, 'fullHeight' => false])

@php
    $base = 'bg-white rounded shadow-sm p-6';
    $height = $fullHeight ? 'h-full min-h-0 flex flex-col' : '';
@endphp

<div {{ $attributes->merge(['class' => "$base $height"]) }}>
    @if ($title)
        <h2 class="text-lg font-semibold mb-4">{{ $title }}</h2>
    @endif

    {{ $slot }}
</div>
