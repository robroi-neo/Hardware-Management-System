@props(['disabled' => false])

@php
    // Check if the overall Laravel $errors bag has an error for this input's name
    $hasError = $errors->has($attributes->get('name'));

    // Swap the classes based on the error status
    $themeClass = $hasError 
        ? 'border-red-500 focus:border-red-600 focus:ring-red-600 text-red-900' 
        : 'border-gray-300 bg-white focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<input @disabled($disabled) {{ $attributes->merge(['class' => $themeClass . ' rounded shadow-sm transition-colors']) }}>