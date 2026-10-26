@props([
    'name',
    'label' => null,
    'type' => 'text',
    'model' => null,
    'placeholder' => '',
    'required' => false,
])

<div class="mb-4">
    <label class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        @if($model) x-model="{{ $model }}" @endif
        placeholder="{{ $placeholder }}"
        {{-- Alpine dynamically swaps border colors based on the error state --}}
        :class="errors?.{{ $name }} 
            ? 'border-red-500 bg-red-0  focus:border-red-600 focus:ring-1 focus:ring-red-600 text-red-900 placeholder-red-300' 
            : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'"
        class="mt-1 w-full rounded border px-3 py-2 text-sm focus:outline-none transition-colors"
    />

    {{-- Validation error (Alpine / Laravel hybrid) --}}
    <template x-if="errors?.{{ $name }}">
        <p class="mt-1 text-sm text-red-600" x-text="errors.{{ $name }}[0]"></p>
    </template>
</div>
