@php
    // Module cards: title, one line, and the inner SVG of a 24x24 stroke icon.
    $modules = [
        ['Point of sale', 'Ring up sales, search products by name, and refund with a recorded reason.',
            '<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M2 3h3l2.7 12.4a1 1 0 0 0 1 .8h9.6a1 1 0 0 0 1-.8L21 7H6"/>'],
        ['Inventory', 'Per-branch stock levels, low-stock alerts, manual stock in/out and a full movement log.',
            '<path d="M21 8 12 3 3 8v8l9 5 9-5V8ZM3 8l9 5 9-5M12 13v8"/>'],
        ['Purchasing', 'Create supplier invoices that add stock automatically, and track invoice history.',
            '<path d="M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1ZM8 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M8 11h8M8 15h5"/>'],
        ['Suppliers', 'Keep supplier contacts in one list and deactivate the ones you no longer buy from.',
            '<path d="M3 7h13v10H3zM16 10h3l2 3v4h-5M7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>'],
        ['Users & roles', 'Admin, Manager and Cashier roles, each with its own permissions.',
            '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 11l2 2 4-4"/>'],
        ['Audit logs', 'See who did what, and when, across user activity and system events.',
            '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6ZM14 3v6h6M8 13h8M8 17h8"/>'],
    ];

    $branches = [
        ['Branch 1', 'Hardware and Motor Parts', 'Provincial Rd, Mawab, Davao de Oro'],
        ['Branch 2', 'Tiles and Flooring Division', 'Provincial Rd, Mawab, Davao de Oro'],
    ];

    $steps = [
        ['Pick a role', 'Admin, Manager or Cashier. Each sees different modules.'],
        ['Pick a sample branch', 'Hardware & Motor Parts or Tiles & Flooring, each with its own stock.'],
        ['Click around', 'Ring up a sale, receive stock, issue a refund. You can’t break anything.'],
    ];
@endphp

<x-layouts.marketing
    title="RNM-HMS · Live demo of a hardware store management system"
    description="Point of sale, inventory, purchasing and suppliers for a two-division hardware store. Try the live demo with sample data."
>
    {{-- Top bar --}}
    <header class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-5">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
            <span class="h-10 w-10 [&>svg]:h-10 [&>svg]:w-10"><x-application-logo /></span>
            <span class="text-base font-semibold text-indigo-950">RNM-HMS</span>
        </a>
        <nav aria-label="Primary" class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
            <a href="#modules" class="text-slate-700 hover:text-indigo-700">Modules</a>
            <a href="#branches" class="text-slate-700 hover:text-indigo-700">Branches</a>
            <a href="#how" class="text-slate-700 hover:text-indigo-700">How the demo works</a>
            <a href="{{ route('demo') }}" class="rounded-lg bg-[#4E55F0] px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Try the demo</a>
        </nav>
    </header>

    {{-- Hero --}}
    <section class="mx-auto flex max-w-6xl flex-wrap items-center gap-12 px-6 pb-20 pt-14">
        <div class="flex min-w-0 flex-[1_1_420px] flex-col gap-5">
            <span class="self-start rounded-full bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-800">Built for Milaran’s Hardware · Mawab, Davao de Oro</span>
            <h1 class="text-4xl font-bold leading-[1.08] tracking-tight text-indigo-950 sm:text-5xl">Two divisions. One counter. Every bolt and tile accounted for.</h1>
            <p class="max-w-xl text-lg leading-relaxed text-slate-600">Point of sale, inventory, purchasing and suppliers in one system, with stock tracked separately for the hardware store and the tiles division.</p>
            <div class="mt-2 flex flex-wrap gap-3">
                <a href="{{ route('demo') }}" class="inline-flex min-h-[52px] items-center gap-2.5 rounded-xl bg-[#4E55F0] px-6 font-semibold text-white hover:bg-indigo-700">
                    Try the live demo
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="#modules" class="inline-flex min-h-[52px] items-center rounded-xl border border-slate-300 bg-white px-6 font-semibold text-indigo-950 hover:bg-slate-50">See what’s inside</a>
            </div>
            <p class="text-sm text-slate-500">No sign-up. Runs on sample data that resets every hour.</p>
        </div>

        {{-- App preview: a drawn sketch of the dashboard, not a screenshot --}}
        <div aria-hidden="true" class="min-w-0 flex-[1_1_480px] rounded-2xl bg-indigo-950 p-3.5 shadow-[0_30px_60px_rgba(30,27,75,0.25)]">
            <div class="flex gap-1.5 px-1.5 pb-3 pt-1">
                <span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span><span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span><span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span>
            </div>
            <div class="flex min-h-[340px] overflow-hidden rounded-xl bg-slate-100">
                <div class="flex basis-1/4 flex-col gap-2 bg-indigo-900 px-2.5 py-4">
                    <span class="h-2.5 w-3/4 rounded bg-white/50"></span>
                    <span class="mt-3.5 h-6 rounded-md bg-[#4E55F0]"></span>
                    <span class="h-6 rounded-md bg-white/10"></span>
                    <span class="h-6 rounded-md bg-white/10"></span>
                    <span class="h-6 rounded-md bg-white/10"></span>
                    <span class="h-6 rounded-md bg-white/10"></span>
                </div>
                <div class="flex flex-1 flex-col gap-3 p-4">
                    <span class="h-3.5 w-2/5 rounded bg-slate-300"></span>
                    <div class="grid grid-cols-3 gap-2.5">
                        @foreach (['w-4/5', 'w-3/4', 'w-1/2'] as $width)
                            <div class="flex flex-col gap-2 rounded-lg bg-white p-3"><span class="h-2 w-3/5 rounded bg-slate-200"></span><span class="h-4 {{ $width }} rounded bg-indigo-300"></span></div>
                        @endforeach
                    </div>
                    <div class="flex flex-1 items-end gap-2.5 rounded-lg bg-white p-3.5">
                        @foreach (['40%', '65%', '50%', '85%', '60%', '72%'] as $i => $height)
                            <span class="flex-1 rounded-t {{ $i === 3 ? 'bg-[#4E55F0]' : 'bg-indigo-200' }}" style="height: {{ $height }}"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modules --}}
    <section id="modules" class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 py-20">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-semibold text-indigo-950 sm:text-4xl">Everything the store runs on</h2>
                <p class="mt-3 leading-relaxed text-slate-600">Each role only sees the modules it’s allowed to. Switch roles in the demo to compare.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($modules as [$name, $line, $icon])
                    <article class="flex flex-col gap-2.5 rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                            <svg class="h-[22px] w-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                        </span>
                        <h3 class="text-lg font-semibold">{{ $name }}</h3>
                        <p class="text-sm leading-relaxed text-slate-600">{{ $line }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Branches --}}
    <section id="branches" class="mx-auto flex max-w-6xl flex-wrap items-center gap-12 px-6 py-20">
        <div class="flex flex-[1_1_360px] flex-col gap-3.5">
            <h2 class="text-3xl font-semibold text-indigo-950 sm:text-4xl">Built around branches</h2>
            <p class="leading-relaxed text-slate-600">Sales, stock movements and invoices are filed under the branch you pick, so the hardware store and the tiles division never mix numbers. In the demo, try both and compare their stock.</p>
        </div>
        <div class="grid flex-[1_1_480px] grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($branches as [$label, $name, $address])
                <article class="flex flex-col gap-2 rounded-2xl border border-slate-200 p-6">
                    <span class="text-xs font-semibold uppercase tracking-wider text-indigo-700">{{ $label }}</span>
                    <h3 class="text-lg font-semibold">{{ $name }}</h3>
                    <p class="text-sm text-slate-600">{{ $address }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- How the demo works --}}
    <section id="how" class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-6xl flex-col gap-9 px-6 py-20">
            <h2 class="text-3xl font-semibold text-indigo-950 sm:text-4xl">The demo in three steps</h2>
            <ol class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($steps as $i => [$name, $line])
                    <li class="flex flex-col gap-2.5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-950 font-semibold text-white">{{ $i + 1 }}</span>
                        <h3 class="text-lg font-semibold">{{ $name }}</h3>
                        <p class="text-sm leading-relaxed text-slate-600">{{ $line }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Closing call to action --}}
    <section class="mx-auto max-w-6xl px-6 pb-20 pt-16">
        <div class="flex flex-wrap items-center justify-between gap-6 rounded-3xl bg-indigo-950 p-10 text-white sm:p-12">
            <div class="flex max-w-xl flex-col gap-2">
                <h2 class="text-2xl font-semibold sm:text-3xl">See it with sample data</h2>
                <p class="leading-relaxed text-slate-300">Three roles, two branches, resets every hour.</p>
            </div>
            <a href="{{ route('demo') }}" class="inline-flex min-h-[52px] items-center rounded-xl bg-white px-6 font-semibold text-indigo-950 hover:bg-indigo-50">Open the live demo</a>
        </div>
    </section>

    <footer class="border-t border-slate-200">
        <div class="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-6 py-7 text-sm text-slate-500">
            {{-- TODO: replace with the team / course credit --}}
            <span>RNM Hardware Management System · [Team / course credit]</span>
            <a href="https://github.com/robroi-neo/Hardware-Management-System" class="text-indigo-700 hover:text-indigo-900">Source on GitHub</a>
        </div>
    </footer>
</x-layouts.marketing>
