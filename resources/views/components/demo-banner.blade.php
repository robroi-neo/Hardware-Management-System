{{--
    Demo banner shown on every app page while in the demo.
    $demo comes from the DemoContext middleware.
--}}
@isset($demo)
    <div role="region" aria-label="Demo controls"
         x-data="demoBanner(@js($demo['nextResetAt']), @js($demo['role']), {{ count($demo['tasks']) }})"
         class="border-b border-amber-300 bg-amber-100 text-amber-950">
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-2 text-sm sm:px-6">
            <p class="flex items-center gap-2">
                <svg class="h-[18px] w-[18px] flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6M10 3v6L4.5 18.5A1.7 1.7 0 0 0 6 21h12a1.7 1.7 0 0 0 1.5-2.5L14 9V3"/></svg>
                <span>
                    <strong class="font-semibold">Demo mode</strong>
                    · {{ $demo['roleName'] }} at {{ $demo['branch'] ?? 'no branch' }}
                    · sample data <span x-text="resetLabel">resets hourly</span>
                </span>
            </p>
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                        class="inline-flex min-h-[36px] items-center gap-1.5 rounded-lg border border-amber-600 px-3 text-[13px] font-medium hover:bg-amber-200">
                    Try these <span class="tabular-nums" x-text="`${done.length}/${total}`"></span>
                </button>
                <a href="{{ route('demo', ['role' => $demo['role']]) }}"
                   class="inline-flex min-h-[36px] items-center rounded-lg border border-amber-600 px-3 text-[13px] font-medium hover:bg-amber-200">Switch role</a>
                @if (Route::has('demo.reset'))
                    <form method="POST" action="{{ route('demo.reset') }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-[36px] items-center rounded-lg border border-amber-600 px-3 text-[13px] font-medium hover:bg-amber-200">Reset data</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-[36px] items-center rounded-lg bg-amber-800 px-3 text-[13px] font-semibold text-white hover:bg-amber-900">Exit demo</button>
                </form>
            </div>
        </div>

        {{-- "Try these" checklist --}}
        <div x-show="open" x-cloak x-transition.opacity class="border-t border-amber-200 bg-amber-50 px-4 py-3 sm:px-6">
            <ol class="flex flex-col gap-1 sm:flex-row sm:flex-wrap sm:gap-x-6">
                @foreach ($demo['tasks'] as $i => $task)
                    <li class="flex items-center gap-2.5 py-1 text-sm">
                        <input type="checkbox" id="demo-task-{{ $i }}" :checked="done.includes({{ $i }})" @change="toggle({{ $i }})"
                               class="h-4 w-4 rounded border-amber-500 text-amber-700 focus:ring-amber-500">
                        <label for="demo-task-{{ $i }}" :class="done.includes({{ $i }}) && 'text-amber-700 line-through'">{{ $task['label'] }}</label>
                        <a href="{{ $task['url'] }}" class="text-[13px] font-medium text-indigo-700 underline-offset-2 hover:underline">Go</a>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                // Countdown to the next reset and the checklist ticks (kept per browser).
                function demoBanner(nextResetAt, role, total) {
                    const key = `demo-tasks-${role}`;
                    const read = () => { try { return JSON.parse(localStorage.getItem(key)) || []; } catch (e) { return []; } };
                    return {
                        open: false,
                        total,
                        done: read(),
                        resetLabel: 'resets hourly',
                        init() {
                            const update = () => {
                                const minutes = Math.ceil((new Date(nextResetAt) - Date.now()) / 60000);
                                this.resetLabel = minutes > 0 ? `resets in ${minutes} min` : 'resets on your next click';
                            };
                            update();
                            setInterval(update, 30000);
                        },
                        toggle(i) {
                            this.done = this.done.includes(i) ? this.done.filter(x => x !== i) : [...this.done, i];
                            try { localStorage.setItem(key, JSON.stringify(this.done)); } catch (e) {}
                        },
                    };
                }
            </script>
        @endpush
    @endonce
@endisset
