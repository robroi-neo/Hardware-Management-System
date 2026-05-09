<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Supplier Management</h2>
    </x-slot>

    <div
        x-data="supplierManager()"
        class=""
    >
        <!-- Header with Search and Create Button -->
        <x-card>
            <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h3 class="text-lg font-semibold text-slate-900">Suppliers List</h3>
                @can('suppliers.create')
                <button
                    type="button"
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Supplier
                </button>
                @endcan
            </div>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by name, contact, phone, or email..."
                    class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                />
                <select
                    name="status"
                    class="rounded border border-slate-300 pl-4 pr-10 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                >
                    <option value="">All Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button
                    type="submit"
                    class="rounded bg-slate-200 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-300"
                >
                    Filter
                </button>
            </form>

            <!-- Suppliers Table -->
            <div class="mt-6 overflow-hidden rounded border border-slate-200 bg-white">
                @if ($suppliers->count() > 0)
                    <table class="w-full">
                        <thead class="border-b border-slate-200 bg-indigo-50">
                        <tr>
                            <x-table.sortable-header
                                label="Supplier Name"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="supplier_name"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <x-table.sortable-header
                                label="Contact Person"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="contact_person"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <x-table.sortable-header
                                label="Contact Number"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="contact_number"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <x-table.sortable-header
                                label="Status"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="status"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <th class="px-6 py-3 text-left text-base font-normal text-slate-700">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @foreach ($suppliers as $supplier)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                    <button
                                        type="button"
                                        @click="openDetailModal({{ $supplier->toJson() }})"
                                        class="text-indigo-600 hover:text-indigo-700 hover:underline"
                                    >
                                        {{ $supplier->supplier_name }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $supplier->contact_person ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $supplier->contact_number ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($supplier->status === 'active')
                                        <span class="inline-flex items-center rounded bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal({{ $supplier->toJson() }})"
                                            @can('suppliers.edit')
                                                class="text-indigo-600 hover:text-indigo-700 transition-colors"
                                            @else
                                                disabled
                                                class="cursor-not-allowed text-slate-400"
                                            @endcan
                                        >
                                            <svg width="18" height="18" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_586_3461)">
                                                    <!-- Changed fill and stroke to currentColor -->
                                                    <path d="M13.3923 16.0714L8.03516 17.0357L8.92801 11.6071L19.1602 1.41066C19.3262 1.24329 19.5237 1.11044 19.7413 1.01978C19.9589 0.929122 20.1923 0.882446 20.428 0.882446C20.6637 0.882446 20.8972 0.929122 21.1148 1.01978C21.3324 1.11044 21.5299 1.24329 21.6959 1.41066L23.5887 3.30351C23.7561 3.46952 23.8889 3.66702 23.9796 3.88463C24.0703 4.10223 24.1169 4.33564 24.1169 4.57137C24.1169 4.80711 24.0703 5.04051 23.9796 5.25812C23.8889 5.47572 23.7561 5.67322 23.5887 5.83923L13.3923 16.0714Z" fill="currentColor" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M21.4283 16.9643V22.3214C21.4283 22.795 21.2402 23.2492 20.9053 23.5841C20.5704 23.919 20.1162 24.1071 19.6426 24.1071H2.67829C2.20469 24.1071 1.75049 23.919 1.4156 23.5841C1.08072 23.2492 0.892578 22.795 0.892578 22.3214V5.35713C0.892578 4.88352 1.08072 4.42932 1.4156 4.09443C1.75049 3.75955 2.20469 3.57141 2.67829 3.57141H8.03544" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_586_3461">
                                                        <rect width="25" height="25" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </button>
                                        
                                        @can('suppliers.delete')
                                            <button
                                                type="button"
                                                @click="openDeleteModal({{ $supplier->id }}, '{{ addslashes($supplier->supplier_name) }}')"
                                                class="text-red-600 hover:text-red-700 transition-colors"
                                            >
                                                <svg width="20" height="20" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_586_3460)">
                                                        <!-- Changed fill and stroke to currentColor -->
                                                        <path d="M1.78613 6.25H23.2147" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M4.46484 6.25H20.5363V22.3214C20.5363 22.795 20.3481 23.2492 20.0132 23.5841C19.6784 23.919 19.2242 24.1071 18.7506 24.1071H6.25056C5.77696 24.1071 5.32275 23.919 4.98787 23.5841C4.65298 23.2492 4.46484 22.795 4.46484 22.3214V6.25Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M8.03613 6.24997V5.35711C8.03613 4.17311 8.50648 3.0376 9.34369 2.20038C10.1809 1.36317 11.3164 0.892822 12.5004 0.892822C13.6844 0.892822 14.8199 1.36317 15.6571 2.20038C16.4944 3.0376 16.9647 4.17311 16.9647 5.35711V6.24997" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        
                                                        <!-- Left these two white so the details show up inside the red trash can -->
                                                        <path d="M9.82227 9.82141V19.6428" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M15.1787 9.82141V19.6428" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_586_3460">
                                                            <rect width="25" height="25" fill="white"/>
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="border-t border-slate-200 bg-white px-6 py-4">
                        <x-table.pagination :paginator="$suppliers" />
                    </div>
                @else
                    <x-table.empty-state
                        :colspan="5"
                        message="No suppliers found. Create one to get started."
                    />
                @endif
            </div>

        </x-card>

        <!-- Success/Error Messages -->
        @if (session('success'))
        <div class="rounded border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="rounded border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
        @endif


        <!-- Detail Modal (Read-Only View) -->
        <x-modals.detail-modal
            show="showDetailModal"
            data="detail"
            title="Supplier Details"
            :fields="[
                'supplier_name' => 'Supplier Name',
                'contact_person' => 'Contact Person',
                'company_address' => 'Company Address',
                'contact_number' => 'Contact Number',
                'contact_email' => 'Contact Email',
                'status' => 'Status',
            ]"
            :actions="[
                'edit' => [
                    'label' => 'Edit',
                    'permission' => 'suppliers.edit',
                    'method' => 'switchToEdit()',
                ],
            ]"
            onClose="closeDetailModal()"
        />

        <!-- Create/Edit Modal -->
        <x-modals.modal
            show="showModal"
            close="closeModal()"
        >
            <x-slot:header>
                <h3
                    class="text-lg font-semibold"
                    x-text="isEditMode ? 'Edit Supplier' : 'Create Supplier'"
                ></h3>
            </x-slot:header>
            <form
                :action="isEditMode ? `/suppliers/${editingId}` : '{{ route('suppliers.store') }}'"
                method="POST"
                @submit.prevent="submitForm"
            >
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT" />
                </template>

                <!-- Supplier Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Supplier Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="supplier_name"
                        x-model="form.supplier_name"
                        required
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter supplier name"
                    />
                    <template x-if="errors.supplier_name">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.supplier_name[0]"></p>
                    </template>
                </div>

                <!-- Contact Person -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Contact Person</label>
                    <input
                        type="text"
                        name="contact_person"
                        x-model="form.contact_person"
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter contact person name"
                    />
                </div>

                <!-- Company Address -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Company Address</label>
                    <textarea
                        name="company_address"
                        x-model="form.company_address"
                        rows="3"
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter company address"
                    ></textarea>
                </div>

                <!-- Contact Number -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Contact Number</label>
                    <input
                        type="tel"
                        name="contact_number"
                        x-model="form.contact_number"
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter contact number"
                    />
                </div>

                <!-- Contact Email -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700">Contact Email</label>
                    <input
                        type="email"
                        name="contact_email"
                        x-model="form.contact_email"
                        class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        placeholder="Enter email address"
                    />
                    <template x-if="errors.contact_email">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.contact_email[0]"></p>
                    </template>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
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
            <!-- Modal Actions -->

        </x-modals.modal>

        <!-- Delete Confirmation Modal -->
        <div
            x-show="showDeleteModal"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeDeleteModal()"
        >
            <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Delete Supplier</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to delete <strong x-text="deleteSupplierName"></strong>? This action cannot be undone.
                </p>

                <form
                    :action="`/suppliers/${deleteId}`"
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
    </div>

    <script>
        function supplierManager() {
            return {
                showModal: false,
                showDetailModal: false,
                showDeleteModal: false,
                isEditMode: false,
                editingId: null,
                deleteId: null,
                deleteSupplierName: '',
                errors: {},
                detail: {},
                form: {
                    supplier_name: '',
                    contact_person: '',
                    company_address: '',
                    contact_number: '',
                    contact_email: '',
                    status: 'active',
                },

                openCreateModal() {
                    this.isEditMode = false;
                    this.editingId = null;
                    this.form = {
                        supplier_name: '',
                        contact_person: '',
                        company_address: '',
                        contact_number: '',
                        contact_email: '',
                        status: 'active',
                    };
                    this.errors = {};
                    this.showModal = true;
                },

                openEditModal(supplier) {
                    this.isEditMode = true;
                    this.editingId = supplier.id;
                    this.form = {
                        supplier_name: supplier.supplier_name,
                        contact_person: supplier.contact_person || '',
                        company_address: supplier.company_address || '',
                        contact_number: supplier.contact_number || '',
                        contact_email: supplier.contact_email || '',
                        status: supplier.status,
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
                    this.deleteSupplierName = name;
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteId = null;
                    this.deleteSupplierName = '';
                },

                openDetailModal(supplier) {
                    this.detail = supplier;
                    this.showDetailModal = true;
                },

                closeDetailModal() {
                    this.showDetailModal = false;
                    this.detail = {};
                },

                switchToEdit() {
                    this.closeDetailModal();
                    this.openEditModal(this.detail);
                },

                async submitForm() {

                    console.log('submitted')
                    this.errors = {};

                    const url = this.isEditMode
                        ? `/suppliers/${this.editingId}`
                        : `/suppliers`;

                    const method = this.isEditMode
                        ? 'PUT'
                        : 'POST';

                    try {

                        const response = await fetch(url, {
                            method: method,
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

                        // Validation failed
                        if (!response.ok) {

                            if (response.status === 422) {
                                this.errors = data.errors;
                                return;
                            }

                            throw new Error('Something went wrong');
                        }

                        // Success
                        this.closeModal();

                        // Optional:
                        window.location.reload();

                    } catch (error) {
                        console.error(error);
                    }
                }
            };
        }
    </script>
</x-app-layout>
