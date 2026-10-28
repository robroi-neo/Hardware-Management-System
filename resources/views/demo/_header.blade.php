{{-- Top bar shared by the demo entry screens --}}
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-6 py-3.5">
        <a href="{{ route('landing') }}" class="flex min-h-[44px] items-center gap-2.5 text-indigo-950">
            <span class="h-8 w-8 [&>svg]:h-8 [&>svg]:w-8"><x-application-logo /></span>
            <span class="text-[15px] font-semibold">HMS</span>
        </a>
        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">Live demo · sample data</span>
    </div>
</header>

@if (session('error'))
    <div role="alert" class="mx-auto mt-6 max-w-6xl px-6">
        <p class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('error') }}</p>
    </div>
@endif
