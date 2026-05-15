<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Manual Stock In</h2>
    </x-slot>

    <x-card>
        <div x-data="stockInApp()" class="space-y-6">
            <!-- Branch Selection -->
            <div>
                <label class="block text-sm font-medium text-slate-700">Select Branch</label>
                <select
                    x-model="form.branch_id"
                    class="mt-2 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">-- Select a Branch --</option>
                    @if($isAdmin)
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    @else
                        <option value="{{ $userDefaultBranchId }}" selected>{{ \App\Models\Branch::find($userDefaultBranchId)?->name }}</option>
                    @endif
                </select>
            </div>

            <!-- Product Search -->
            <div>
                <label class="block text-sm font-medium text-slate-700">Search & Add Products</label>
                <div class="mt-2 flex gap-2">
                    <div class="relative flex-1">
                        <x-product-search-typeahead />
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-slate-200 rounded overflow-hidden">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 font-medium text-sm text-slate-700">
                    Stock-In Items
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-indigo-100 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-700">Product ID</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-700">Product Name</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-700">Unit</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-700">Unit Cost</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-700">Quantity</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-700">Subtotal</th>
                                <th class="px-4 py-3 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="form.items.length === 0">
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                        No items added yet. Search and select products above.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="(item, index) in form.items" :key="index">
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="px-4 py-3" x-text="item.product_id"></td>
                                    <td class="px-4 py-3 text-slate-700" x-text="item.product_name"></td>
                                    <td class="px-4 py-3 text-slate-600" x-text="item.unit"></td>
                                    <td class="px-4 py-3 text-right text-slate-600">₱<span x-text="formatPrice(item.unit_cost)"></span></td>
                                    <td class="px-4 py-3 text-right">
                                        <input
                                            type="number"
                                            x-model.number="item.quantity"
                                            min="0.01"
                                            step="0.01"
                                            @input="calculateTotal(index)"
                                            class="w-24 rounded border border-slate-300 px-2 py-1 text-sm text-right focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900">₱<span x-text="formatPrice(item.subtotal)"></span></td>
                                    <td class="px-4 py-3 text-center">
                                        <button
                                            type="button"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            title="Remove item"
                                        >
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                                    </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
                    <div class="flex justify-end gap-8">
                        <div>
                            <p class="text-sm text-slate-600">Total Items:</p>
                            <p class="text-2xl font-semibold text-slate-900"><span x-text="getTotalQuantity()"></span></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600">Total Cost:</p>
                            <p class="text-2xl font-semibold text-slate-900">₱<span x-text="formatPrice(getTotalCost())"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reference Type & Notes -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Reference Type</label>
                    <select
                        x-model="form.reference_type"
                        class="mt-2 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">-- Select --</option>
                        <option value="purchase">Purchase/Invoice</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Reference ID (Optional)</label>
                    <input
                        type="number"
                        x-model="form.reference_id"
                        placeholder="Invoice/Document ID"
                        class="mt-2 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Notes (Optional)</label>
                <textarea
                    x-model="form.notes"
                    placeholder="Add any additional notes about this stock-in..."
                    rows="3"
                    class="mt-2 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                ></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end">
                <button
                    type="button"
                    @click="resetForm()"
                    class="rounded border border-slate-300 px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Clear
                </button>
                <button
                    type="button"
                    @click="$dispatch('open-modal', 'confirm-stock-in')"
                    :disabled="!canSubmit()"
                    class="rounded bg-green-600 px-6 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Stock-In
                </button>
            </div>

            <!-- Success/Error Messages -->
            <template x-if="message">
                <div :class="messageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" class="rounded border p-4">
                    <p class="text-sm font-medium" x-text="message"></p>
                </div>
            </template>

            <!-- Stock-In Confirmation Modal -->
            <x-modal name="confirm-stock-in" maxWidth="md" focusable>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">Confirm Stock-In</h3>
                        <button type="button" @click="$dispatch('close-modal', 'confirm-stock-in')" class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-6">
                        <p class="text-sm text-slate-600">
                            Are you sure you want to complete this stock-in? You are adding <strong x-text="getTotalQuantity()" class="text-slate-900"></strong> items with a total cost of <strong class="text-slate-900">₱<span x-text="formatPrice(getTotalCost())"></span></strong>.
                        </p>
                        <p class="text-sm text-amber-600 mt-2 font-medium">
                            This action will immediately update your inventory levels.
                        </p>
                    </div>
                    
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="$dispatch('close-modal', 'confirm-stock-in')"
                            class="px-4 py-2 rounded border border-gray-300 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                            :disabled="submitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="submitForm()"
                            :disabled="submitting"
                            class="px-4 py-2 rounded bg-green-600 text-white text-sm font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span x-show="!submitting">Confirm Stock-In</span>
                            <span x-show="submitting">Processing...</span>
                        </button>
                    </div>
                </div>
            </x-modal>

        </div>
    </x-card>

    @push('scripts')
    <script>
        function stockInApp() {
            return {
                form: {
                    branch_id: {{ $userDefaultBranchId ?? 'null' }},
                    items: [],
                    reference_type: '',
                    reference_id: null,
                    notes: '',
                },
                typeahead: {
                    q: '',
                    open: false,
                    loading: false,
                    items: [],
                    activeIndex: -1,
                    debounceTimer: null,
                },
                submitting: false,
                message: '',
                messageType: 'success',

                onTypeaheadInput() {
                    clearTimeout(this.typeahead.debounceTimer);

                    if (!this.typeahead.q.trim()) {
                        this.typeahead.items = [];
                        this.typeahead.open = false;
                        return;
                    }

                    this.typeahead.loading = true;
                    this.typeahead.open = true;

                    this.typeahead.debounceTimer = setTimeout(async () => {
                        try {
                            const response = await fetch(`{{ route('inventory.api.products.search') }}?q=${encodeURIComponent(this.typeahead.q)}&limit=10`);
                            const data = await response.json();
                            this.typeahead.items = data;
                            this.typeahead.activeIndex = -1;
                        } finally {
                            this.typeahead.loading = false;
                        }
                    }, 250);
                },

                moveTypeahead(direction) {
                    const nextIndex = this.typeahead.activeIndex + direction;
                    if (nextIndex >= -1 && nextIndex < this.typeahead.items.length) {
                        this.typeahead.activeIndex = nextIndex;
                    }
                },

                onTypeaheadEnter() {
                    if (this.typeahead.items.length === 0) {
                        return;
                    }

                    const index = this.typeahead.activeIndex >= 0 ? this.typeahead.activeIndex : 0;
                    this.selectTypeaheadItem(index);
                },

                selectTypeaheadItem(index) {
                    if (index < 0 || index >= this.typeahead.items.length) {
                        return;
                    }

                    this.selectProduct(this.typeahead.items[index]);
                    this.typeahead.q = '';
                    this.typeahead.items = [];
                    this.typeahead.open = false;
                    this.typeahead.activeIndex = -1;
                },

                closeTypeahead() {
                    this.typeahead.open = false;
                },

                reopenTypeahead() {
                    if (this.typeahead.q.trim()) {
                        this.typeahead.open = true;
                    }
                },

                selectProduct(product) {
                    // Check if product already in items
                    const exists = this.form.items.some(item => item.product_id === product.id);
                    if (exists) {
                        this.showToast('Product already added to list.', 'warning');
                        return;
                    }

                    this.form.items.push({
                        product_id: product.id,
                        product_name: product.name,
                        unit: product.unit,
                        unit_cost: product.capital,
                        quantity: 1,
                        subtotal: product.capital,
                    });
                },

                calculateTotal(index) {
                    const item = this.form.items[index];
                    item.subtotal = item.quantity * item.unit_cost;
                },

                removeItem(index) {
                    this.form.items.splice(index, 1);
                },

                getTotalQuantity() {
                    return this.form.items.reduce((sum, item) => sum + parseFloat(item.quantity || 0), 0).toFixed(2);
                },

                getTotalCost() {
                    return this.form.items.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0).toFixed(2);
                },

                canSubmit() {
                    return this.form.branch_id && this.form.items.length > 0 && !this.submitting;
                },

                formatPrice(price) {
                    return parseFloat(price || 0).toFixed(2);
                },

                async submitForm() {
                    if (!this.canSubmit()) return;

                    this.submitting = true;
                    this.message = '';

                    try {
                        const response = await fetch('{{ route("inventory.api.stock-in.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                branch_id: parseInt(this.form.branch_id),
                                items: this.form.items.map(item => ({
                                    product_id: item.product_id,
                                    quantity: parseFloat(item.quantity),
                                })),
                                reference_type: this.form.reference_type || null,
                                reference_id: this.form.reference_id || null,
                                notes: this.form.notes || null,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            // Close the modal
                            this.$dispatch('close-modal', 'confirm-stock-in');
                            
                            // CALL THE TOAST HERE!
                            this.showToast(data.message, 'success');
                            setTimeout(() => this.resetForm(), 2000);
                        } else {
                            // AND HERE!
                            this.showToast(data.message || 'Error processing stock-in', 'error');
                        }
                    } catch (error) {
                        // AND HERE!
                        this.showToast('Error: ' + error.message, 'error');
                    } finally {
                        this.submitting = false;
                    }
                },

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

                resetForm() {
                    this.form = {
                        branch_id: {{ $userDefaultBranchId ?? 'null' }},
                        items: [],
                        reference_type: '',
                        reference_id: null,
                        notes: '',
                    };
                    this.message = '';
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
