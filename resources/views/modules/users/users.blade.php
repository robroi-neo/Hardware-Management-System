<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">User</h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div x-data="userManager()">
        <x-card title="User Management" fullHeight>
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="w-full lg:max-w-md">
                        <x-product-search-typeahead
                            searchInputRef="userSearchInput"
                            placeholder="Search users by name or phone..."
                            primaryField="name"
                            secondaryField="phone"
                            :showMeta="false"
                            :showClearX="false"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="applySearch()"
                            class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Search
                        </button>
                        <button
                            type="button"
                            @click="clearSearch()"
                            class="rounded border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Clear
                        </button>
                    </div>
                </div>
                <form method="GET">
                    <div class="flex items-center gap-2">
                        <x-filters.dropdown-filter
                            :items="$statuses"
                            :selected="$filterStatus"
                            name="status"
                            label="Filter by Status"
                            placeholder="All Statuses"
                            valueField="value"
                            displayField="label"
                            :autoSubmit="true"
                        />
                    </div>
                </form>
            </div>

            <div id="users-table-container" class="overflow-hidden rounded border border-slate-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-indigo-100 text-left text-slate-700">
                        <tr>
                            <x-table.sortable-header
                                label="Name"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="name"
                                route="users.index"
                                :params="['search' => $search, 'status' => $filterStatus]"
                            />
                            <x-table.sortable-header
                                label="Phone"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="phone"
                                route="users.index"
                                :params="['search' => $search, 'status' => $filterStatus]"
                            />
                            <th class="px-4 py-3 font-medium">Branch</th>

                            <x-table.sortable-header
                                label="Role"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="branch"
                                route="users.index"
                                :params="['search' => $search, 'status' => $filterStatus]"
                            />
                            <x-table.sortable-header
                                label="Status"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="status"
                                route="users.index"
                                :params="['search' => $search, 'status' => $filterStatus]"
                            />

                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-700">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-t border-slate-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900">
                                <button
                                    type="button"
                                    @click="openDetailModal({{ $user->toJson() }})"
                                    class="text-indigo-600 hover:text-indigo-700 hover:underline"
                                >
                                    {{ $user->name }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->phone }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->branch?->name }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $user->getRoleNames()->join(', ') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($user->status === 'active')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="openEditModal({{ $user->toJson() }})"
                                        @can('users.edit')
                                            class="text-indigo-600 hover:text-indigo-700"
                                        @else
                                            disabled
                                        class="cursor-not-allowed text-slate-400"
                                        @endcan
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg>
                                    </button>
                                    @can('users.delete')
                                        @if($user->status === 'inactive')
                                            <button
                                                type="button"
                                                @click="openActivateModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                class="text-emerald-600 hover:text-emerald-700"
                                                title="Activate User"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-check-icon lucide-user-check"><path d="m16 11 2 2 4-4"/><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                @click="openDeactivateModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                class="text-amber-600 hover:text-amber-700"
                                                title="Deactivate User"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-minus-icon lucide-user-minus"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-table.empty-state :colspan="6" message="No users found." />
                    @endforelse
                    </tbody>
                </table>
            </div>
            <x-table.pagination :paginator="$users" />
        </x-card>

        <x-modals.detail-modal
            show="showDetailModal"
            x-cloak
            data="detail"
            title="User Details"
            :fields="[
            'name'   => 'Name',
            'phone'  => 'Phone',
            'status' => 'Status',
            'branch.name'  => 'Branch',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ]"
            :actions="[
            'edit' => [
                'label'      => 'Edit',
                'permission' => 'users.edit',
                'method'     => 'switchToEdit()',
            ],
        ]"
            onClose="closeDetailModal()"
        />

        <x-modals.modal
            show="showModal"
            x-cloak
            close="closeModal()"
        >
            <x-slot:header>
                <h3
                    class="text-lg font-semibold"
                    x-text="isEditMode ? 'Edit User' : 'Create User'"
                ></h3>
            </x-slot:header>

            <form
                :action="isEditMode ? `/users/${editingId}` : '{{ route('users.store') }}'"
                method="POST"
                @submit.prevent="submitForm"
            >
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT" />
                </template>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        x-model="form.name"
                        required
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter full name"
                    />
                    <template x-if="errors.name">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Phone</label>
                    <input
                        type="tel"
                        name="phone"
                        x-model="form.phone"
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter phone number"
                    />
                    <template x-if="errors.phone">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.phone[0]"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="role"
                        x-model="form.role"
                        required
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="">— Select a role —</option>
                        @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.role">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.role[0]"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Branch</label>
                    <select
                        name="branch_id"
                        x-model="form.branch_id"
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="">— No branch —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.branch_id">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.branch_id[0]"></p>
                    </template>
                </div>

                <template x-if="!isEditMode">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="status"
                            x-model="form.status"
                            class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </template>

                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="closeModal()"
                        class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        <span x-text="isEditMode ? 'Update' : 'Create'"></span>
                    </button>
                </div>
            </form>
        </x-modals.modal>

        <div
            x-show="showDeactivateModal"
            x-transition
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeDeactivateModal()"
        >
            <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Deactivate User</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to deactivate <strong x-text="deactivateUserName"></strong>?
                </p>
                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="closeDeactivateModal()"
                        class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="deactivateUser()"
                        class="flex-1 rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Deactivate
                    </button>
                </div>
            </div>
        </div>

        <div
            x-show="showActivateModal"
            x-transition
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeActivateModal()"
        >
            <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Activate User</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to activate <strong x-text="activateUserName"></strong>?
                </p>
                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="closeActivateModal()"
                        class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="activateUser()"
                        class="flex-1 rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                        Activate
                    </button>
                </div>
            </div>
        </div>

        <script>
            function userManager() {
                return {
                    showModal: false,
                    showDetailModal: false,
                    showDeactivateModal: false,
                    showActivateModal: false,

                    isEditMode: false,

                    editingId: null,
                    deactivateId: null,
                    activateId: null,

                    deactivateUserName: '',
                    activateUserName: '',

                    errors: {},
                    detail: {},

                    sortBy: @json($sortBy),
                    sortDir: @json($sortDir),
                    filterStatus: @json($filterStatus),

                    typeahead: {
                        q: @json($search ?? ''),
                        items: [],
                        open: false,
                        loading: false,
                        activeIndex: -1,
                        debounceHandle: null,
                        limit: 8,
                    },

                    form: {
                        name: '',
                        phone: '',
                        role: '',
                        branch_id: '',
                        status: 'active',
                    },

                    // 1. ADDED TOAST FUNCTION
                    showToast(message, type = 'success') {
                        const toast = document.createElement('div');
                        
                        toast.className = `fixed bottom-6 right-6 px-6 py-4 rounded border shadow-2xl z-[99999] font-medium text-sm transition-all duration-300 transform translate-y-0 opacity-100 flex items-center gap-3 ${
                            type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 
                            type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : 
                            'bg-red-50 border-red-200 text-red-800'
                        }`;

                        let icon = '';
                        if (type === 'success') {
                            icon = `<svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                        } else if (type === 'warning') {
                            icon = `<svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>`;
                        } else {
                            icon = `<svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                        }
                        
                        toast.innerHTML = `${icon} <span>${message}</span>`;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.classList.remove('translate-y-0', 'opacity-100');
                            toast.classList.add('translate-y-4', 'opacity-0');
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    },

                    // 2. ADDED BACKGROUND REFRESH FUNCTION
                    async refreshTable() {
                        try {
                            const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const html = await response.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            document.querySelector('#users-table-container').innerHTML = doc.querySelector('#users-table-container').innerHTML;
                        } catch (error) {
                            console.error('Seamless refresh failed, falling back to hard reload.', error);
                            window.location.reload();
                        }
                    },

                    openCreateModal() {
                        this.isEditMode = false;
                        this.editingId = null;

                        this.form = {
                            name: '',
                            phone: '',
                            role: '',
                            branch_id: '',
                            status: 'active',
                        };

                        this.errors = {};
                        this.showModal = true;
                    },

                    openEditModal(user) {
                        this.isEditMode = true;
                        this.editingId = user.id;

                        this.form = {
                            name: user.name || '',
                            phone: user.phone || '',
                            role: user.roles?.[0]?.name || '',
                            branch_id: user.branch_id || '',
                            status: user.status || 'active',
                        };

                        this.errors = {};
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        this.isEditMode = false;
                        this.editingId = null;
                        this.errors = {};
                    },

                    openDeactivateModal(id, name) {
                        this.deactivateId = id;
                        this.deactivateUserName = name;
                        this.showDeactivateModal = true;
                    },

                    closeDeactivateModal() {
                        this.showDeactivateModal = false;
                        this.deactivateId = null;
                        this.deactivateUserName = '';
                    },

                    openActivateModal(id, name) {
                        this.activateId = id;
                        this.activateUserName = name;
                        this.showActivateModal = true;
                    },

                    closeActivateModal() {
                        this.showActivateModal = false;
                        this.activateId = null;
                        this.activateUserName = '';
                    },

                    // 3. UPDATED DEACTIVATE FUNCTION
                    async deactivateUser() {
                        try {
                            const response = await fetch(`/users/${this.deactivateId}/deactivate`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document
                                        .querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Failed to deactivate user');
                            }

                            this.closeDeactivateModal();
                            this.showToast('User deactivated successfully.', 'warning');
                            await this.refreshTable();

                        } catch (error) {
                            console.error(error);
                            this.showToast('Failed to deactivate user.', 'error');
                        }
                    },

                    // 4. UPDATED ACTIVATE FUNCTION
                    async activateUser() {
                        try {
                            const response = await fetch(`/users/${this.activateId}/activate`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document
                                        .querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Failed to activate user');
                            }

                            this.closeActivateModal();
                            this.showToast('User activated successfully.', 'success');
                            await this.refreshTable();

                        } catch (error) {
                            console.error(error);
                            this.showToast('Failed to activate user.', 'error');
                        }
                    },

                    openDetailModal(user) {
                        this.detail = user;
                        this.showDetailModal = true;
                    },

                    closeDetailModal() {
                        this.showDetailModal = false;
                        this.detail = {};
                    },

                    switchToEdit() {
                        const user = this.detail;

                        this.closeDetailModal();
                        this.openEditModal(user);
                    },

                    onTypeaheadInput() {
                        if (this.typeahead.debounceHandle) {
                            clearTimeout(this.typeahead.debounceHandle);
                        }

                        const query = this.typeahead.q.trim();
                        if (!query) {
                            this.typeahead.items = [];
                            this.typeahead.open = false;
                            this.typeahead.activeIndex = -1;
                            return;
                        }

                        this.typeahead.debounceHandle = setTimeout(() => {
                            this.fetchTypeahead(query);
                        }, 250);
                    },

                    async fetchTypeahead(query) {
                        this.typeahead.loading = true;
                        this.typeahead.open = true;

                        const params = new URLSearchParams({
                            q: query,
                            limit: String(this.typeahead.limit),
                        });

                        if (this.filterStatus) {
                            params.set('status', this.filterStatus);
                        }

                        try {
                            const response = await fetch(`{{ route('users.api.search') }}?${params.toString()}`);
                            const data = await response.json();

                            this.typeahead.items = Array.isArray(data) ? data : [];
                            this.typeahead.activeIndex = this.typeahead.items.length > 0 ? 0 : -1;
                        } catch (error) {
                            this.typeahead.items = [];
                            this.typeahead.activeIndex = -1;
                            console.error(error);
                        } finally {
                            this.typeahead.loading = false;
                        }
                    },

                    reopenTypeahead() {
                        if (this.typeahead.items.length > 0 || this.typeahead.loading) {
                            this.typeahead.open = true;
                        }
                    },

                    closeTypeahead() {
                        this.typeahead.open = false;
                        this.typeahead.activeIndex = -1;
                    },

                    moveTypeahead(step) {
                        if (!this.typeahead.open || this.typeahead.items.length === 0) {
                            return;
                        }

                        const count = this.typeahead.items.length;
                        const current = this.typeahead.activeIndex < 0 ? 0 : this.typeahead.activeIndex;
                        this.typeahead.activeIndex = (current + step + count) % count;
                    },

                    onTypeaheadEnter() {
                        if (this.typeahead.open && this.typeahead.items.length > 0) {
                            const index = this.typeahead.activeIndex >= 0 ? this.typeahead.activeIndex : 0;
                            this.selectTypeaheadItem(index);
                            return;
                        }

                        this.applySearch();
                    },

                    selectTypeaheadItem(index) {
                        const user = this.typeahead.items[index];
                        if (!user) {
                            return;
                        }

                        this.typeahead.q = user.name || String(user.id ?? '');
                        this.typeahead.items = [];
                        this.closeTypeahead();

                        this.applySearch();
                    },

                    applySearch() {
                        const query = this.typeahead.q.trim();
                        const params = new URLSearchParams();

                        if (query) {
                            params.set('search', query);
                        }
                        if (this.filterStatus) {
                            params.set('status', this.filterStatus);
                        }
                        if (this.sortBy) {
                            params.set('sort_by', this.sortBy);
                        }
                        if (this.sortDir) {
                            params.set('sort_dir', this.sortDir);
                        }

                        const url = params.toString()
                            ? `{{ route('users.index') }}?${params.toString()}`
                            : `{{ route('users.index') }}`;

                        window.location = url;
                    },

                    clearSearch() {
                        this.typeahead.q = '';
                        const params = new URLSearchParams();

                        if (this.filterStatus) {
                            params.set('status', this.filterStatus);
                        }
                        if (this.sortBy) {
                            params.set('sort_by', this.sortBy);
                        }
                        if (this.sortDir) {
                            params.set('sort_dir', this.sortDir);
                        }

                        const url = params.toString()
                            ? `{{ route('users.index') }}?${params.toString()}`
                            : `{{ route('users.index') }}`;

                        window.location = url;
                    },

                    // 5. UPDATED SUBMIT FORM FUNCTION
                    async submitForm() {
                        this.errors = {};

                        const url = this.isEditMode
                            ? `/users/${this.editingId}`
                            : `/users`;

                        const method = this.isEditMode
                            ? 'PUT'
                            : 'POST';

                        const successMessage = this.isEditMode ? 'User successfully updated!' : 'User successfully added!';

                        try {
                            const response = await fetch(url, {
                                method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document
                                        .querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                },
                                body: JSON.stringify(this.form),
                            });

                            const contentType = response.headers.get("content-type");
                            if (contentType && contentType.indexOf("application/json") !== -1) {
                                const data = await response.json();
                                if (!response.ok) {
                                    if (response.status === 422) {
                                        this.errors = data.errors;
                                        return;
                                    }
                                    throw new Error('Something went wrong');
                                }
                            } else if (!response.ok) {
                                throw new Error('Server returned an error');
                            }

                            this.closeModal();
                            this.showToast(successMessage, 'success');
                            await this.refreshTable();

                        } catch (error) {
                            console.error(error);
                            this.showToast('An error occurred while saving the user.', 'error');
                        }
                    },
                };
            }
        </script>
    </div>{{-- end x-data="userManager()" --}}
</x-app-layout>