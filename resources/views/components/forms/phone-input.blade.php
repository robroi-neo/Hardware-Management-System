@props([
    'name' => 'contact_number',
    'label' => 'Contact Number',
    'model' => 'form.contact_number',
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
        type="tel"
        name="{{ $name }}"
        x-model="{{ $model }}"
        @input="
            let value = $event.target.value.replace(/\D/g, '').slice(0, 11);
            {{ $model }} = value;
        "
        placeholder="09123456789"
        autocomplete="tel"
        {{-- Alpine dynamically swaps border colors based on the error state --}}
        :class="errors?.{{ $name }} 
            ? 'border-red-500 focus:border-red-600 focus:ring-1 focus:ring-red-600 text-red-900 placeholder-red-300' 
            : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'"
        class="mt-1 w-full rounded border px-3 py-2 text-sm focus:outline-none transition-colors"
    />

    {{-- Server-side validation error (From Laravel) --}}
    <template x-if="errors?.{{ $name }}">
        <p class="mt-1 text-sm text-red-600" x-text="errors.{{ $name }}[0]"></p>
    </template>

    {{-- Client-side Alpine validation (Only shows while typing if there is no server error) --}}
    <p class="mt-1 text-sm font-medium text-amber-600"
       x-show="!errors?.{{ $name }} && {{ $model }} && String({{ $model }}).length > 0 && String({{ $model }}).length !== 11"
       style="display: none;"
    >
        Must be exactly 11 digits
    </p>
</div>