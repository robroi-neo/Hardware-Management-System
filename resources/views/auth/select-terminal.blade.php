<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-slate-900">Select Terminal</h2>
        <p class="mt-1 text-sm text-slate-600">Choose terminal before logging in.</p>
    </div>

    <form id="terminalForm" method="POST" action="{{ route('terminal.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="terminal_id" :value="__('Terminal')" />
            <select id="terminal_id" name="terminal_id" class="mt-1 block w-full rounded border-slate-300" required>
                <option value="">-- Select terminal --</option>
                @foreach ($terminals as $terminal)
                    <option value="{{ $terminal->id }}" @selected(old('terminal_id') == $terminal->id)>
                        T{{ $terminal->terminal_id }} - {{ $terminal->terminal_name }} ({{ $terminal->branch?->name }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('terminal_id')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            Continue to Login
        </x-primary-button>
    </form>

    <script>
        (() => {
            const key = 'pos_terminal_id';
            const form = document.getElementById('terminalForm');
            const select = document.getElementById('terminal_id');

            form?.addEventListener('submit', () => {
                if (select?.value) {
                    sessionStorage.setItem(key, select.value);
                }
            });

            const saved = sessionStorage.getItem(key);
            if (saved && !select.value) {
                const exists = Array.from(select.options).some((opt) => opt.value === saved);
                if (exists) {
                    select.value = saved;
                }
            }
        })();
    </script>
</x-guest-layout>

