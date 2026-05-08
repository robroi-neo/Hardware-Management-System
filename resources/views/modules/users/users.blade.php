<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">User</h2>
    </x-slot>

    <x-card title="User Management" fullHeight>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>

                        <th class="px-4 py-3 font-semibold">ID</th>
                        <th class="px-4 py-3 font-semibold">Name</th>
                        <th class="px-4 py-3 font-semibold">Phone</th>
                        <th class="px-4 py-3 font-semibold">Role</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Branch</th>
                        <th class="px-4 py-3 font-semibold">Created at</th>
                        <th class="px-4 py-3 font-semibold">Updated at</th>

                    </tr>
                </thead>
                <tbody class="divide-y border-t border-slate-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900">#{{ $user->id }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->phone }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $user->getRoleNames()->join(', ') }}
                            </td>
                            <td class="px-4 py-3 text-slate-900">{{ $user->created_at }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->updated_at }}</td>
                        </tr>
                    @empty
                        <x-table.empty-state :colspan="4" message="No users found." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
