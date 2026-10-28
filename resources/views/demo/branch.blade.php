<x-layouts.marketing title="Live demo · Pick a branch · HMS">
    <div class="flex min-h-screen flex-col bg-slate-100">
        @include('demo._header')

        <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 px-6 py-12">
            <div class="flex flex-col gap-2.5">
                <p class="text-[13px] font-semibold uppercase tracking-wider text-indigo-700">Step 2 of 2</p>
                <h1 class="text-3xl font-semibold text-indigo-950 sm:text-[34px]">Which branch are you working at?</h1>
                <p class="text-[15px] text-slate-600">Real staff choose this at the start of every shift. Each branch has its own stock, sales and invoices.</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-indigo-100 text-[13px] font-semibold text-indigo-800">{{ \Illuminate\Support\Str::substr($roleName, 0, 1) }}</span>
                <span class="flex-1 text-sm text-slate-700">Exploring as <strong class="font-semibold text-slate-900">{{ $roleName }}</strong></span>
                <a href="{{ route('demo', ['role' => $role]) }}" class="inline-flex min-h-[44px] items-center px-1 text-sm font-medium text-indigo-700 hover:text-indigo-900">Change role</a>
            </div>

            <div class="flex flex-col gap-3.5">
                @foreach ($branches as $branch)
                    <form method="POST" action="{{ route('demo.start') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ $role }}">
                        <input type="hidden" name="branch_id" value="{{ $branch['id'] }}">
                        <button type="submit" class="flex min-h-[112px] w-full items-center gap-5 rounded-2xl border-2 border-slate-200 bg-white p-6 text-left transition hover:border-[#4E55F0] focus-visible:border-[#4E55F0] focus-visible:outline-none">
                            <span class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700">
                                @if ($branch['icon'] === 'tiles')
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/></svg>
                                @else
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2.4-.6-.6-2.4 2.6-2.6Z"/></svg>
                                @endif
                            </span>
                            <span class="flex min-w-0 flex-1 flex-col gap-1">
                                <span class="text-[17px] font-semibold text-slate-900">{{ $branch['name'] }}</span>
                                <span class="text-[13px] text-slate-600">{{ $branch['line'] }} · {{ $branch['products'] }} sample products</span>
                            </span>
                            <svg class="h-[22px] w-[22px] flex-none text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </form>
                @endforeach
            </div>

            <a href="{{ route('demo', ['role' => $role]) }}" class="inline-flex min-h-[44px] items-center gap-2 self-start text-[15px] font-medium text-indigo-700 hover:text-indigo-900">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back
            </a>
        </main>
    </div>
</x-layouts.marketing>
