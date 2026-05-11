<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">User</h2>
    </x-slot>

    <div x-data="userManager()">
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
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-700">
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
                            <td class="px-4 py-3 text-slate-600">
                                {{ $user->getRoleNames()->join(', ') }}
                            </td>
                            <td class="px-4 py-3 text-slate-900">{{ $user->status }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->branch?->name }}</td>
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
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L18 8.625" />
                                        </svg>
                                    </button>
                                    @can('users.delete')
                                        <button
                                            type="button"
                                            @click="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-table.empty-state :colspan="6" message="No users found." />
                    @endforelse
                    </tbody>
                </table>
                <div class="mt-4 px-4 pb-4">
                    {{ $users->links() }}
                </div>
            </div>
        </x-card>

        <!-- Detail Modal (Read-Only View) -->
        <x-modals.detail-modal
            show="showDetailModal"
            data="detail"
            title="User Details"
            :fields="[
            'name'   => 'Name',
            'phone'  => 'Phone',
            'status' => 'Status',
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

        <!-- Create / Edit Modal -->
        <x-modals.modal
            show="showModal"
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

                <!-- Name -->
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

                <!-- Phone -->
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

                <!-- Role -->
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

                <!-- Branch -->
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

                <!-- Status -->
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

        <!-- Delete Confirmation Modal -->
        <div
            x-show="showDeleteModal"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeDeleteModal()"
        >
            <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Delete User</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to delete <strong x-text="deleteUserName"></strong>? This action cannot be undone.
                </p>

                <form
                    :action="`/users/${deleteId}`"
                    method="POST"
                    class="flex gap-3"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        @click="closeDeleteModal()"
                        class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <script>
            function userManager() {
                return {
                    showModal: false,
                    showDetailModal: false,
                    showDeleteModal: false,
                    isEditMode: false,
                    editingId: null,
                    deleteId: null,
                    deleteUserName: '',
                    errors: {},
                    detail: {},
                    form: {
                        name: '',
                        phone: '',
                        role: '',
                        branch_id: '',
                        status: 'active',
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
                            name:      user.name      || '',
                            phone:     user.phone     || '',
                            role:      user.roles?.[0]?.name || '',
                            branch_id: user.branch_id || '',
                            status:    user.status    || 'active',
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

                    openDeleteModal(id, name) {
                        this.deleteId = id;
                        this.deleteUserName = name;
                        this.showDeleteModal = true;
                    },

                    closeDeleteModal() {
                        this.showDeleteModal = false;
                        this.deleteId = null;
                        this.deleteUserName = '';
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

                    async submitForm() {
                        this.errors = {};

                        const url    = this.isEditMode ? `/users/${this.editingId}` : `/users`;
                        const method = this.isEditMode ? 'PUT' : 'POST';

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

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422) {
                                    this.errors = data.errors;
                                    return;
                                }
                                throw new Error('Something went wrong');
                            }

                            this.closeModal();
                            window.location.reload();

                        } catch (error) {
                            console.error(error);
                        }
                    },
                };
            }
        </script>
    </div>{{-- end x-data="userManager()" --}}
</x-app-layout>
