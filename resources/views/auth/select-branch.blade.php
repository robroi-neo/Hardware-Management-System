<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-slate-900">Select branch</h2>
        <p class="mt-1 text-sm text-slate-600">Choose branch before logging in.</p>
    </div>

    <form id="branchForm" method="POST" action="{{ route('branch.store') }}" class="space-y-4">
        @csrf

        <div>
            <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded border-slate-300" required>
                <option value="">-- Select branch --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                        {{ $branch->id }} - {{ $branch->name }} ({{ $branch->address }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500">
            Continue to Login
        </x-primary-button>
    </form>

    <script>
        (() => {
            const key = 'branch';
            const form = document.getElementById('branchForm');
            const select = document.getElementById('branch_id');

            form?.addEventListener('submit', () => {
                if (select?.value) {
                    sessionStorage.setItem(key, select.value);
                }
            });

            const saved = sessionStorage.getItem(key);
            if (saved && !select.value) {
                console.log(select.value)
                const exists = Array.from(select.options).some((opt) => opt.value === saved);
                if (exists) {
                    select.value = saved;
                }
            }
        })();
    </script>
</x-guest-layout>

