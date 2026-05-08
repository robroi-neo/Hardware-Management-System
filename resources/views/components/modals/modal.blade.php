<div
    x-show="{{ $show }}"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="{{ $close }}"
>
    <div class="w-full {{ $maxWidth ?? 'max-w-md' }} rounded-lg bg-white shadow-lg">

        {{-- Header --}}
        <div class="border-b px-6 py-4">
            {{ $header }}
        </div>

        {{-- Body --}}
        <div class="p-6">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @isset($footer)
            <div class="border-t px-6 py-4">
                {{ $footer }}
            </div>
        @endisset

    </div>
</div>
