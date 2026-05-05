<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">New Invoice</h2>
    </x-slot>

    <div x-data="purchasingApp()" x-init="initCart()" class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full min-h-0">
        <!-- Main Content -->
        <section class="lg:col-span-2 space-y-6">
            <!-- Supplier & Branch Selection -->
            <div class="bg-white rounded shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Supplier & Branch</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Terminal & Branch Info (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Terminal & Branch</label>
                        <div class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 bg-slate-50 text-slate-700">
                            <div class="font-medium text-sm">{{ $terminalName }}</div>
                            <div class="text-xs text-slate-600">{{ $selectedBranch->name ?? 'Unknown Branch' }}</div>
                        </div>
                    </div>

                    <!-- Supplier Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Supplier *</label>
                        <select
                            x-model="selectedSupplier"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @change="handleSupplierChange"
                        >
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Supplier Contact Details -->
                    <div x-show="selectedSupplier" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md md:col-span-2">
                        <div class="text-sm font-semibold text-blue-900 mb-3">Supplier Contact Information</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="font-medium text-blue-800 mb-1">Contact Person</div>
                                <div class="text-blue-700" x-text="getSupplierContact('contact_person')"></div>
                            </div>
                            <div>
                                <div class="font-medium text-blue-800 mb-1">Contact Number</div>
                                <div class="text-blue-700" x-text="getSupplierContact('contact_number')"></div>
                            </div>
                            <div>
                                <div class="font-medium text-blue-800 mb-1">Email</div>
                                <div class="text-blue-700" x-text="getSupplierContact('contact_email')"></div>
                            </div>
                            <div>
                                <div class="font-medium text-blue-800 mb-1">Company Address</div>
                                <div class="text-blue-700" x-text="getSupplierContact('company_address')"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Search & Cart -->
            <div class="bg-white rounded shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Add Products</h3>

                <!-- Product Search Section -->
                <div class="mb-6 pb-6 border-b">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Search or Create Products</label>
                    <div class="flex gap-3" :class="{ 'opacity-50 pointer-events-none': !selectedSupplier || !selectedBranch }">
                        <div class="flex-1">
                            <x-product-search-typeahead />
                        </div>
                        <button
                            @click="openProductModal()"
                            class="px-4 py-2 bg-indigo-900 text-white text-sm rounded hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed font-medium whitespace-nowrap"
                            :disabled="!selectedSupplier || !selectedBranch"
                            title="Create a new product"
                        >
                            <span>+</span> New Product
                        </button>
                    </div>

                    <!-- No Results Message -->
                    <div x-show="typeahead.q.length > 0 && typeahead.items.length === 0 && !typeahead.loading"
                         class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md text-sm text-amber-800">
                        No products found for "<span class="font-semibold" x-text="typeahead.q"></span>"
                    </div>


                </div>

                <!-- Cart Table -->
                <div class="border border-gray-200 rounded overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-gray-600 bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Product Id</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Unit</th>
                                    <th class="px-4 py-3">Quantity</th>
                                    <th class="px-4 py-3">Unit Price</th>
                                    <th class="px-4 py-3">Subtotal</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="(item, idx) in cartItems" :key="item.product_id">
                                    <tr>
                                        <td class="px-4 py-3 font-medium" x-text="item.product_id"></td>
                                        <td class="px-4 py-3 font-medium" x-text="item.product_name"></td>
                                        <td class="px-4 py-3" x-text="item.product_unit"></td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                :value="item.quantity"
                                                @change="updateCartItem(item.product_id, 'quantity', $event.target.value)"
                                                class="w-20 border-gray-300 rounded px-2 py-1 text-sm"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                :value="item.unit_price"
                                                @change="updateCartItem(item.product_id, 'unit_price', $event.target.value)"
                                                class="w-24 border-gray-300 rounded px-2 py-1 text-sm"
                                            />
                                        </td>
                                        <td class="px-4 py-3 font-medium">₱<span x-text="formatPrice(item.quantity * item.unit_price)"></span></td>
                                        <td class="px-4 py-3 text-right">
                                            <button @click="removeCartItem(item.product_id)" class="text-sm text-red-500 hover:text-red-700">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="cartItems.length === 0">
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                        No products added. Search above or create a new product to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Summary Sidebar -->
        <aside class="bg-white rounded shadow-sm p-6 h-fit">
            <h3 class="text-lg font-semibold mb-4">Summary</h3>

            <div class="space-y-3 mb-6">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium">₱<span x-text="formatPrice(cartSubtotal)"></span></span>
                </div>
                <div class="border-t pt-3 flex justify-between text-lg">
                    <span class="font-semibold">Total:</span>
                    <span class="font-semibold text-indigo-900">₱<span x-text="formatPrice(cartSubtotal)"></span></span>
                </div>
            </div>

            <div class="mb-6 p-4 bg-blue-50 rounded text-sm text-blue-700">
                <div class="font-medium mb-2">Invoice Details:</div>
                <div>Due in 30 days from today</div>
                <div x-show="selectedSupplier && selectedBranch">
                    <div class="text-xs text-gray-600 mt-2">
                        Ready to checkout with <span x-text="cartItems.length"></span> item(s)
                    </div>
                </div>
            </div>

            <button
                @click="proceedToCheckout"
                :disabled="!selectedSupplier || !selectedBranch || cartItems.length === 0 || isProcessing"
                class="w-full bg-indigo-900 text-white py-3 rounded hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
            >
                <span x-show="!isProcessing">Proceed to Checkout</span>
                <span x-show="isProcessing">Processing...</span>
            </button>

            <button
                @click="resetCart"
                class="w-full mt-3 border border-gray-300 text-gray-700 py-2 rounded hover:bg-gray-50"
            >
                Clear Cart
            </button>
        </aside>
        <!-- Product Creation Modal -->
        <x-modal name="create-product" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Create New Product</h3>
                    <button @click="$dispatch('close-modal', 'create-product')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
                </div>

                <div class="space-y-4">
                    <!-- Product Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Product Name *</label>
                        <input
                            type="text"
                            x-model="newProduct.name"
                            @input="previewStandardizedName"
                            placeholder="e.g., Hammer Claw"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <div x-show="standardizedNamePreview" class="mt-2 p-3 bg-blue-50 rounded text-sm">
                            <div class="text-gray-600">Standardized as:</div>
                            <div class="font-mono font-semibold text-blue-900" x-text="standardizedNamePreview"></div>
                            <div x-show="productNameExists" class="mt-2 text-red-600 text-xs">
                                ⚠️ A product with this name already exists
                            </div>
                        </div>
                    </div>

                    <!-- Unit -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Unit *</label>
                        <select x-model="newProduct.unit" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Unit --</option>
                            <option value="pcs">pcs (pieces)</option>
                            <option value="box">box</option>
                            <option value="meter">meter</option>
                            <option value="liter">liter</option>
                            <option value="kg">kg (kilogram)</option>
                            <option value="gram">gram</option>
                        </select>
                    </div>

                    <!-- Capital (Cost Price) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Capital (Cost Price) *</label>
                        <div class="flex items-center">
                            <span class="text-gray-600 mr-2">₱</span>
                            <input
                                type="number"
                                x-model.number="newProduct.capital"
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <p x-show="productCreateError" class="mt-3 text-sm text-red-600" x-text="productCreateError"></p>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button
                            @click="$dispatch('close-modal', 'create-product')"
                            class="px-4 py-2 border rounded text-sm text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveNewProduct"
                            :disabled="!newProduct.name || !newProduct.unit || !newProduct.capital || isCreatingProduct || productNameExists"
                            class="px-4 py-2 rounded bg-indigo-900 text-white text-sm hover:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Create Product
                            <span x-show="!isCreatingProduct">Create Product</span>
                            <span x-show="isCreatingProduct">Creating...</span>
                        </button>
                    </div>
                </div>
            </div>
        </x-modal>

        <!-- Checkout Modal -->
        <x-modal name="checkout-confirm" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Confirm Purchase</h3>
                    <button @click="$dispatch('close-modal', 'checkout-confirm')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Amount:</span>
                        <span class="text-2xl font-semibold text-indigo-900">₱<span x-text="formatPrice(cartSubtotal)"></span></span>
                    </div>
                    <!-- This is not being filled? why-->
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Items:</span>
                        <span x-text="cartItems.length"></span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Invoice Due Date:</span>
                        <span x-text="invoiceDueDate"></span>
                    </div>
                </div>

                <div class="mb-4 p-3 bg-yellow-50 rounded text-sm text-yellow-700">
                    This will create a Purchase order and Invoice. Inventory will be updated accordingly.
                </div>

                <p x-show="checkoutError" class="mb-4 text-sm text-red-600" x-text="checkoutError"></p>

                <div class="flex items-center justify-end gap-3">
                    <button
                        @click="$dispatch('close-modal', 'checkout-confirm')"
                        class="px-4 py-2 border rounded text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="completeCheckout"
                        :disabled="isProcessing"
                        class="px-4 py-2 rounded bg-indigo-900 text-white text-sm hover:bg-indigo-800 disabled:opacity-50"
                    >
                        Confirm & Pay
                        <span x-show="!isProcessing">Complete Purchase</span>
                        <span x-show="isProcessing">Processing...</span>
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- Success Modal -->
        <x-modal name="checkout-success" maxWidth="md" focusable>
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Purchase Successful!</h3>
                </div>

                <div class="space-y-3 mb-6 p-4 bg-slate-50 rounded text-sm">
                    <div>
                        <span class="text-slate-600">Purchase ID:</span>
                        <span class="font-mono font-medium" x-text="successData.purchase_id"></span>
                    </div>
                    <div>
                        <span class="text-slate-600">Invoice ID:</span>
                        <span class="font-mono font-medium" x-text="successData.invoice_id"></span>
                    </div>
                    <div>
                        <span class="text-slate-600">Total Amount:</span>
                        <span class="font-medium">₱<span x-text="formatPrice(successData.total_amount)"></span></span>
                    </div>
                    <div>
                        <span class="text-slate-600">Items:</span>
                        <span x-text="successData.items_count"></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        @click="$dispatch('close-modal', 'checkout-success'); newInvoice()"
                        class="px-4 py-2 rounded bg-indigo-900 text-white text-sm hover:bg-indigo-800"
                    >
                        Create Another Invoice
                    </button>
                </div>
            </div>
        </x-modal>

    </div>


    <script>
        function purchasingApp() {
            return {
                selectedSupplier: @json($selectedSupplierId ?? ''),
                selectedBranch: '{{ $branchId }}',  // Auto-set from session
                suppliers: @json($suppliers),  // Add this for contact details
                typeahead: {
                    q: '',
                    open: false,
                    loading: false,
                    items: [],
                    activeIndex: -1,
                    debounceTimer: null,
                },
                cartItems: [],
                cartSubtotal: 0,
                newProduct: {
                    name: '',
                    unit: '',
                    capital: '',
                },
                standardizedNamePreview: '',
                productNameExists: false,
                isProcessing: false,
                isCreatingProduct: false,
                productCreateError: '',
                checkoutError: '',
                successData: {},
                invoiceDueDate: '',

                async initCart() {
                    await this.refreshCartFromServer();
                },

                async refreshCartFromServer() {
                    try {
                        const response = await fetch(`{{ route('purchasing.api.checkout.prepare') }}`);
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            if (data.message === 'Cart is empty') {
                                this.cartItems = [];
                                this.cartSubtotal = 0;
                                return;
                            }
                            throw new Error(data.message || 'Failed to load cart');
                        }

                        this.cartItems = (data.data.items || []).map(item => ({
                            product_id: item.product_id,
                            product_name: item.product_name,
                            product_unit: item.unit,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                        }));
                        this.cartSubtotal = data.data.total || 0;
                    } catch (error) {
                        console.error('Failed to refresh cart:', error);
                    }
                },

                async postCart(url, payload) {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Cart update failed');
                    }

                    return data;
                },

                async postSupplierSelection(supplierId) {
                    const payload = supplierId ? { supplier_id: parseInt(supplierId) } : { supplier_id: null };
                    await this.postCart(`{{ route('purchasing.api.supplier.set') }}`, payload);
                },

                async handleSupplierChange() {
                    try {
                        await this.postSupplierSelection(this.selectedSupplier);
                    } catch (error) {
                        console.error('Failed to update supplier:', error);
                    }

                    await this.resetCart(false);
                },

                async onTypeaheadInput() {
                    if (!this.selectedSupplier || !this.selectedBranch) {
                        this.typeahead.items = [];
                        this.typeahead.open = false;
                        return;
                    }

                    clearTimeout(this.typeahead.debounceTimer);

                    if (this.typeahead.q.length < 1) {
                        this.typeahead.open = false;
                        this.typeahead.items = [];
                        return;
                    }

                    this.typeahead.loading = true;
                    this.typeahead.open = true;

                    this.typeahead.debounceTimer = setTimeout(async () => {
                        try {
                            const response = await fetch(`{{ route('purchasing.api.products.search') }}?q=${encodeURIComponent(this.typeahead.q)}`);
                            const data = await response.json();
                            this.typeahead.items = data.data || [];
                            this.typeahead.activeIndex = -1;
                        } catch (error) {
                            console.error('Product search failed:', error);
                            this.typeahead.items = [];
                        } finally {
                            this.typeahead.loading = false;
                        }
                    }, 300);
                },

                moveTypeahead(direction) {
                    const newIndex = this.typeahead.activeIndex + direction;
                    if (newIndex >= -1 && newIndex < this.typeahead.items.length) {
                        this.typeahead.activeIndex = newIndex;
                    }
                },

                selectTypeaheadItem(index) {
                    if (index >= 0 && index < this.typeahead.items.length) {
                        this.addProductToCart(this.typeahead.items[index]);
                        this.typeahead.q = '';
                        this.typeahead.items = [];
                        this.typeahead.open = false;
                        this.typeahead.activeIndex = -1;
                    }
                },

                closeTypeahead() {
                    this.typeahead.open = false;
                },

                reopenTypeahead() {
                    if (this.typeahead.q.length > 0 && this.typeahead.items.length > 0) {
                        this.typeahead.open = true;
                    }
                },

                async previewStandardizedName() {
                    if (!this.newProduct.name) {
                        this.standardizedNamePreview = '';
                        this.productNameExists = false;
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('purchasing.api.products.preview') }}?name=${encodeURIComponent(this.newProduct.name)}`);
                        const data = await response.json();
                        this.standardizedNamePreview = data.standardized;
                        this.productNameExists = data.exists;
                    } catch (error) {
                        console.error('Preview failed:', error);
                    }
                },

                async saveNewProduct() {
                    if (!this.newProduct.name || !this.newProduct.unit || !this.newProduct.capital) {
                        this.productCreateError = 'All fields are required';
                        return;
                    }

                    this.isCreatingProduct = true;
                    this.productCreateError = '';

                    try {
                        const response = await fetch(`{{ route('purchasing.api.products.store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                name: this.newProduct.name,
                                unit: this.newProduct.unit,
                                capital: this.newProduct.capital,
                            }),
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Add to cart immediately
                            this.addProductToCart(result.data);

                            // Reset form
                            this.newProduct = { name: '', unit: '', capital: '' };
                            this.standardizedNamePreview = '';
                            this.productNameExists = false;

                            // Close modal
                            this.$dispatch('close-modal', 'create-product');
                        } else {
                            this.productCreateError = result.errors?.name?.[0] || result.message;
                        }
                    } catch (error) {
                        this.productCreateError = 'Failed to create product. Please try again.';
                        console.error(error);
                    } finally {
                        this.isCreatingProduct = false;
                    }
                },

                async addProductToCart(product) {
                    const exists = this.cartItems.find(item => item.product_id === product.id);
                    const nextQuantity = exists ? (parseFloat(exists.quantity) + 1) : 1;
                    const unitPrice = exists?.unit_price ?? product.capital;

                    try {
                        await this.postCart(`{{ route('purchasing.api.cart.add') }}`, {
                            product_id: product.id,
                            quantity: nextQuantity,
                            unit_price: unitPrice,
                        });
                        await this.refreshCartFromServer();
                    } catch (error) {
                        console.error('Failed to add item:', error);
                    }
                },

                async updateCartItem(productId, field, value) {
                    const parsedValue = parseFloat(value);
                    if (Number.isNaN(parsedValue)) {
                        return;
                    }

                    const payload = { product_id: productId };
                    if (field === 'quantity') {
                        payload.quantity = parsedValue;
                    }
                    if (field === 'unit_price') {
                        payload.unit_price = parsedValue;
                    }

                    try {
                        await this.postCart(`{{ route('purchasing.api.cart.update') }}`, payload);
                        await this.refreshCartFromServer();
                    } catch (error) {
                        console.error('Failed to update item:', error);
                    }
                },

                async removeCartItem(productId) {
                    try {
                        await this.postCart(`{{ route('purchasing.api.cart.remove') }}`, {
                            product_id: productId,
                        });
                        await this.refreshCartFromServer();
                    } catch (error) {
                        console.error('Failed to remove item:', error);
                    }
                },

                updateCartTotal() {
                    this.cartSubtotal = this.cartItems.reduce((sum, item) => {
                        return sum + (item.quantity * item.unit_price);
                    }, 0);
                },

                getSupplierContact(field) {
                    if (!this.selectedSupplier) return '-';
                    const supplier = this.suppliers.find(s => s.id == this.selectedSupplier);
                    if (!supplier) return '-';
                    const value = supplier[field];
                    return value || '-';
                },

                openProductModal() {
                    this.$dispatch('open-modal', 'create-product');
                },

                proceedToCheckout() {
                    // return if any required info is missing
                    if (!this.selectedSupplier || !this.selectedBranch || this.cartItems.length === 0) return;


                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 30);
                    this.invoiceDueDate = tomorrow.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                    });

                    this.$dispatch('open-modal', 'checkout-confirm');
                },

                async completeCheckout() {
                    if (!this.selectedSupplier || !this.selectedBranch || this.cartItems.length === 0) return;

                    this.isProcessing = true;
                    this.checkoutError = '';

                    try {
                        // First, prepare checkout
                        const prepareResponse = await fetch(`{{ route('purchasing.api.checkout.prepare') }}`);
                        const prepareData = await prepareResponse.json();

                        if (!prepareData.success) {
                            this.checkoutError = prepareData.message;
                            this.isProcessing = false;
                            return;
                        }

                        // Then finalize
                        const finalizeResponse = await fetch(`{{ route('purchasing.api.checkout.finalize') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                supplier_id: parseInt(this.selectedSupplier),
                                branch_id: parseInt(this.selectedBranch),
                                date_due_offset: 30,
                            }),
                        });

                        const finalizeData = await finalizeResponse.json();

                        if (finalizeData.success) {
                            this.successData = finalizeData.data;
                            this.$dispatch('close-modal', 'checkout-confirm');
                            this.$dispatch('open-modal', 'checkout-success');
                        } else {
                            this.checkoutError = finalizeData.message;
                        }
                    } catch (error) {
                        this.checkoutError = 'Checkout failed. Please try again.';
                        console.error(error);
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async resetCart(clearSupplier = true) {
                    try {
                        await this.postCart(`{{ route('purchasing.api.cart.clear') }}`, {});
                        if (clearSupplier) {
                            await this.postSupplierSelection(null);
                        }
                    } catch (error) {
                        console.error('Failed to clear cart:', error);
                    }

                    if (clearSupplier) {
                        this.selectedSupplier = '';
                    }
                    this.cartItems = [];
                    this.cartSubtotal = 0;
                    this.typeahead.q = '';
                    this.typeahead.items = [];
                    this.typeahead.open = false;
                    this.typeahead.activeIndex = -1;
                },

                async newInvoice() {
                    try {
                        await this.postCart(`{{ route('purchasing.api.cart.clear') }}`, {});
                        await this.postSupplierSelection(null);
                    } catch (error) {
                        console.error('Failed to clear cart:', error);
                    }

                    this.selectedSupplier = '';
                    this.selectedBranch = '';
                    this.cartItems = [];
                    this.cartSubtotal = 0;
                    this.typeahead.q = '';
                    this.typeahead.items = [];
                    this.typeahead.open = false;
                    this.typeahead.activeIndex = -1;
                    this.successData = {};
                    this.checkoutError = '';
                },

                formatPrice(amount) {
                    return parseFloat(amount).toFixed(2);
                },

                formatQty(qty) {
                    return parseFloat(qty).toFixed(2);
                },
            };
        }
    </script>
</x-app-layout>
