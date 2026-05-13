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

            <!-- Search & Filters -->
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="w-full lg:max-w-md">
                        <x-product-search-typeahead
                            searchInputRef="supplierSearchInput"
                            placeholder="Search suppliers by name or phone..."
                            idField="id"
                            primaryField="supplier_name"
                            secondaryField="contact_number"
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
                <div class="flex items-center gap-2">
                    <form>
                        <x-filters.dropdown-filter
                            :items="$statuses"
                            :selected="$status"
                            name="status"
                            label="Filter by Status"
                            valueField="value"
                            displayField="label"
                            :autoSubmit="true"
                        />
                    </form>
                </div>
            </div>

            <!-- Suppliers Table -->
            <div class="overflow-hidden rounded border border-slate-200 bg-white">
                @if ($suppliers->count() > 0)
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-200 bg-indigo-100">
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
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-700">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @foreach ($suppliers as $supplier)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                    <button
                                        type="button"
                                        @click="openDetailModal({{ $supplier->toJson() }})"
                                        class="text-indigo-600 hover:text-indigo-700 hover:underline"
                                    >
                                        {{ $supplier->supplier_name }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $supplier->contact_person ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $supplier->contact_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($supplier->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal({{ $supplier->toJson() }})"
                                            @can('suppliers.edit')
                                                class="text-indigo-600 hover:text-indigo-700"
                                            @else
                                                disabled
                                            class="cursor-not-allowed text-slate-400"
                                            @endcan
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen-icon lucide-square-pen"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg>
                                        </button>
                                        @can('suppliers.delete')
                                            @if ($supplier->status === 'inactive')
                                                <button
                                                    type="button"
                                                    @click="openActivateModal({{ $supplier->id }}, '{{ addslashes($supplier->supplier_name) }}')"
                                                    class="text-emerald-600 hover:text-emerald-700"
                                                    title="Activate Supplier"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-check-icon lucide-package-check"><path d="M12 22V12"/><path d="m16 17 2 2 4-4"/><path d="M21 11.127V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l1.32-.753"/><path d="M3.29 7 12 12l8.71-5"/><path d="m7.5 4.27 8.997 5.148"/></svg>
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="openDeactivateModal({{ $supplier->id }}, '{{ addslashes($supplier->supplier_name) }}')"
                                                    class="text-amber-600 hover:text-amber-700"
                                                    title="Deactivate Supplier"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-minus-icon lucide-package-minus"><path d="M12 22V12"/><path d="M16 17h6"/><path d="M21 13V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l1.675-.955"/><path d="M3.29 7 12 12l8.71-5"/><path d="m7.5 4.27 8.997 5.148"/></svg>
                                                </button>
                                            @endif
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
                <x-forms.input
                    name="supplier_name"
                    label="Supplier Name"
                    model="form.supplier_name"
                    placeholder="Enter supplier name"
                    required
                />

                <x-forms.input
                    name="contact_person"
                    label="Contact Person"
                    model="form.contact_person"
                    placeholder="Enter contact person name"
                />

                <x-forms.textarea
                    name="company_address"
                    label="Company Address"
                    model="form.company_address"
                    placeholder="Enter company address"
                />

                <x-forms.phone-input
                    name="contact_number"
                    label="Contact Number"
                    model="form.contact_number"
                    :required="false"
                />

                <x-forms.input
                    name="contact_email"
                    label="Contact Email"
                    type="email"
                    model="form.contact_email"
                    placeholder="Enter email address"
                />

                <!-- Status -->
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
            <!-- Modal Actions -->

        </x-modals.modal>

        <!-- Deactivate Modal -->
        <div
            x-show="showDeactivateModal"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeDeactivateModal()"
        >
            <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Deactivate Supplier</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to deactivate <strong x-text="deactivateSupplierName"></strong>?
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
                        @click="deactivateSupplier()"
                        class="flex-1 rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Deactivate
                    </button>
                </div>
            </div>
        </div>

        <!-- Activate Modal -->
        <div
            x-show="showActivateModal"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeActivateModal()"
        >
            <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Activate Supplier</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to activate <strong x-text="activateSupplierName"></strong>?
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
                        @click="activateSupplier()"
                        class="flex-1 rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                        Activate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function supplierManager() {
            return {
                showModal: false,
                showDetailModal: false,
                showDeactivateModal: false,
                showActivateModal: false,
                isEditMode: false,
                editingId: null,
                deactivateId: null,
                activateId: null,
                deactivateSupplierName: '',
                activateSupplierName: '',
                errors: {},
                detail: {},
                sortBy: @json($sortBy),
                sortDir: @json($sortDir),
                filterStatus: @json($status),
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

                openDeactivateModal(id, name) {
                    this.deactivateId = id;
                    this.deactivateSupplierName = name;
                    this.showDeactivateModal = true;
                },

                closeDeactivateModal() {
                    this.showDeactivateModal = false;
                    this.deactivateId = null;
                    this.deactivateSupplierName = '';
                },

                openActivateModal(id, name) {
                    this.activateId = id;
                    this.activateSupplierName = name;
                    this.showActivateModal = true;
                },

                closeActivateModal() {
                    this.showActivateModal = false;
                    this.activateId = null;
                    this.activateSupplierName = '';
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

                async deactivateSupplier() {
                    try {
                        const response = await fetch(`/suppliers/${this.deactivateId}/deactivate`, {
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
                            throw new Error('Failed to deactivate supplier');
                        }

                        this.closeDeactivateModal();

                        window.location.reload();

                    } catch (error) {
                        console.error(error);
                    }
                },

                async activateSupplier() {
                    try {
                        const response = await fetch(`/suppliers/${this.activateId}/activate`, {
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
                            throw new Error('Failed to activate supplier');
                        }

                        this.closeActivateModal();

                        window.location.reload();

                    } catch (error) {
                        console.error(error);
                    }
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
                        const response = await fetch(`{{ route('suppliers.api.search') }}?${params.toString()}`);
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
                    const supplier = this.typeahead.items[index];
                    if (!supplier) {
                        return;
                    }

                    this.typeahead.q = supplier.supplier_name || String(supplier.id ?? '');
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
                        ? `{{ route('suppliers.index') }}?${params.toString()}`
                        : `{{ route('suppliers.index') }}`;

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
                        ? `{{ route('suppliers.index') }}?${params.toString()}`
                        : `{{ route('suppliers.index') }}`;

                    window.location = url;
                },

                async submitForm() {
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
