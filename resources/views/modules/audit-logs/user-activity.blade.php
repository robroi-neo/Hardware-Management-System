<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Audit Logs</h2>
    </x-slot>

    <x-card title="User Activity" fullHeight>
        <form method="GET" class="mb-6 flex flex-col items-start gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center">
                <div class="w-full lg:max-w-md">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by user, action, or entity..."
                        class="text-sm placeholder-gray-400 bg-white-100 border border-gray-200 rounded px-3 py-2 pr-8 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    />
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Search
                    </button>
                    <a
                        href="{{ route('audit-logs.user-activity') }}"
                        class="rounded border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Clear
                    </a>
                </div>
            </div>
            <x-filters.dropdown-filter
                :items="$entityTypes"
                :selected="$entityType"
                name="entity_type"
                valueField="value"
                displayField="label"
                label="Filter by Entity"
                onchange="this.form.submit()"
            />
        </form>

        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-100 text-left text-slate-700">
                <tr>
                    <th class="px-4 py-3 font-medium">User</th>
                    <x-table.sortable-header
                        label="Action"
                        :sortBy="$sortBy"
                        :sortDir="$sortDir"
                        column="action"
                        route="audit-logs.user-activity"
                        :params="['search' => $search, 'entity_type' => $entityType]"
                    />
                    <x-table.sortable-header
                        label="Entity"
                        :sortBy="$sortBy"
                        :sortDir="$sortDir"
                        column="entity_type"
                        route="audit-logs.user-activity"
                        :params="['search' => $search, 'entity_type' => $entityType]"
                    />
                    <th class="px-4 py-3 font-medium">Entity ID</th>
                    <th class="px-4 py-3 font-medium">Changes</th>
                    <x-table.sortable-header
                        label="Date"
                        :sortBy="$sortBy"
                        :sortDir="$sortDir"
                        column="created_at"
                        route="audit-logs.user-activity"
                        :params="['search' => $search, 'entity_type' => $entityType]"
                    />
                </tr>
                </thead>
                <tbody class="divide-y border-t border-slate-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-900">
                            {{ $log->user?->name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $log->action ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $log->entity_type ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $log->entity_id ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            @php
                                $oldValues = $log->old_values ?? [];
                                $newValues = $log->new_values ?? [];
                                $keys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                                $changes = [];
                                foreach ($keys as $key) {
                                    $before = $oldValues[$key] ?? null;
                                    $after = $newValues[$key] ?? null;
                                    if ($before === $after) {
                                        continue;
                                    }
                                    $changes[] = sprintf('%s: %s → %s', $key, (string) ($before ?? '—'), (string) ($after ?? '—'));
                                }
                            @endphp
                            @if(count($changes) > 0)
                                <div class="space-y-1">
                                    @foreach($changes as $change)
                                        <div>{{ $change }}</div>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ optional($log->created_at)->format('M d, Y H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <x-table.empty-state :colspan="6" message="No activity logs found." />
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <x-table.pagination :paginator="$logs" />
        </div>
    </x-card>
</x-app-layout>
