@props([
    'name',
    'label' => null,
    'model' => null,
    'rows' => 3,
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

    <textarea
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if($model) x-model="{{ $model }}" @endif
        placeholder="{{ $placeholder }}"
        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
    ></textarea>
</div>
