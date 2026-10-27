<x-layouts.marketing title="Live demo · Pick a role · RNM-HMS">
    <div class="flex min-h-screen flex-col bg-slate-100">
        @include('demo._header')

        <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-7 px-6 py-12">
            <div class="flex flex-col gap-2.5">
                <p class="text-[13px] font-semibold uppercase tracking-wider text-indigo-700">Step 1 of 2</p>
                <h1 class="text-3xl font-semibold text-indigo-950 sm:text-[34px]">Who do you want to be?</h1>
                <p class="max-w-2xl text-[15px] text-slate-600">Each role sees different modules, the same way real staff would. You can switch roles anytime from inside the demo.</p>
            </div>

            <form method="GET" action="{{ route('demo.branch') }}" x-data="{ role: @js($selected), names: @js(collect($roles)->map(fn ($r) => $r['name'])) }" class="flex flex-col gap-7">
                <fieldset>
                    <legend class="sr-only">Role</legend>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach ($roles as $key => $card)
                            <label class="flex min-h-[240px] cursor-pointer flex-col gap-2.5 rounded-2xl border-2 border-slate-200 bg-white p-6 transition has-[:checked]:border-[#4E55F0] has-[:checked]:bg-indigo-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-indigo-400">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="text-xl font-semibold">{{ $card['name'] }}</span>
                                    <input type="radio" name="role" value="{{ $key }}" @checked($key === $selected) x-model="role" class="h-5 w-5 border-slate-400 text-[#4E55F0] focus:ring-0">
                                </span>
                                <span class="text-sm leading-relaxed text-slate-600">{{ $card['blurb'] }}</span>
                                <span class="text-[13px] leading-relaxed text-slate-700"><strong class="font-semibold">Try:</strong> {{ $card['try'] }}</span>
                                <span class="mt-auto flex flex-wrap gap-1.5 pt-2">
                                    @foreach ($card['modules'] as $module)
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $module }}</span>
                                    @endforeach
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('landing') }}" class="inline-flex min-h-[44px] items-center gap-2 text-[15px] font-medium text-indigo-700 hover:text-indigo-900">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                        Back to the overview
                    </a>
                    <button type="submit" class="inline-flex min-h-[52px] items-center gap-2.5 rounded-xl bg-[#4E55F0] px-6 font-semibold text-white hover:bg-indigo-700">
                        Continue as <span x-text="names[role]">{{ $roles[$selected]['name'] }}</span>
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </form>
        </main>
    </div>
</x-layouts.marketing>
