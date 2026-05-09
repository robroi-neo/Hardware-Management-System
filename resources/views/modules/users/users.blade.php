<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">User</h2>
    </x-slot>

    <x-card title="User Management" fullHeight>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <x-table.sortable-header
                            label="Name"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="name"
                            route="users.index"
                            :params="['search' => request('search')]"
                        />
                        <x-table.sortable-header
                            label="Phone"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="phone"
                            route="users.index"
                            :params="['search' => request('search')]"
                        />
                        <th class="px-4 py-3 font-semibold">Role</th>
                        <x-table.sortable-header
                            label="Status"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="status"
                            route="users.index"
                            :params="['search' => request('search')]"
                        />
                        <x-table.sortable-header
                            label="Branch"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="branch"
                            route="users.index"
                            :params="['search' => request('search')]"
                        />
                    </tr>
                </thead>
                <tbody class="divide-y border-t border-slate-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->phone }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $user->getRoleNames()->join(', ') }}
                            </td>
                            <td class="px-4 py-3 text-slate-900">{{ $user->status }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->branch?->name }}</td>
                        </tr>
                    @empty
                        <x-table.empty-state :colspan="4" message="No users found." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
