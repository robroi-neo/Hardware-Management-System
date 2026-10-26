@props([
    'items' => collect(),
    'selected' => null,
    'name' => 'id',
    'label' => 'Select',
    'valueField' => 'id',
    'displayField' => 'name',
    'class' => '',
    'placeholder' => null,
    'autoSubmit' => false,
])

<div class="flex flex-col">
    @if($label)
        <span class="text-xs text-gray-500 mb-1">{{ $label }}</span>
    @endif

    <select
        name="{{ $name }}"
        @if($autoSubmit) onchange="this.form.submit()" @endif
        class="rounded border border-slate-200 pl-3 pr-10 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 hover:border-slate-400 transition-colors {{ $class }}"
        {{ $attributes }}
    >
        <option value="">
            {{ $placeholder ?? $label }}
        </option>

        @foreach($items as $item)
            <option
                value="{{ data_get($item, $valueField) }}"
                {{ (string) $selected === (string) data_get($item, $valueField) ? 'selected' : '' }}
            >
                {{ data_get($item, $displayField) }}
            </option>
        @endforeach
    </select>
</div>
