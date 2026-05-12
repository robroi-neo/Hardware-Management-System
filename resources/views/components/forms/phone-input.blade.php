@props([
    'name' => 'contact_number',
    'label' => 'Contact Number',
    'model' => 'form.contact_number',
    'required' => false,
    'errors' => null,
])

<div class="mb-4">
    <label class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <input
        type="tel"
        name="{{ $name }}"
        x-model="{{ $model }}"
        @input="
            let value = $event.target.value.replace(/\D/g, '').slice(0, 11);
            {{ $model }} = value;
        "
        value="{{ old($name) }}"
        placeholder="09123456789"
        @if($required) required @endif
        autocomplete="tel"
        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none
               {{ $errors && $errors->has($name) ? 'border-red-500' : '' }}"
    />

    {{-- Server-side validation error --}}
    @if($errors && $errors->has($name))
        <p class="text-xs text-red-500 mt-1">{{ $errors->first($name) }}</p>

        {{-- Client-side Alpine validation --}}
    @else
        <p class="text-xs text-red-500 mt-1"
           x-show="{{ $model }} && {{ $model }}.length > 0 && {{ $model }}.length !== 11">
            Must be exactly 11 digits
        </p>
    @endif
</div>
