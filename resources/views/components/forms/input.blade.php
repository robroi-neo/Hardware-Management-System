@props([
    'name',
    'label' => null,
    'type' => 'text',
    'model' => null,
    'placeholder' => '',
    'required' => false,
])

<div class="mb-4">
    @if($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        @if($model) x-model="{{ $model }}" @endif
        placeholder="{{ $placeholder }}"
        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
    />

    {{-- Validation error (Alpine / Laravel hybrid) --}}
    <template x-if="errors?.{{ $name }}">
        <p class="mt-1 text-sm text-red-600" x-text="errors.{{ $name }}[0]"></p>
    </template>
</div>
