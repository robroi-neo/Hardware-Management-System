<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">POS</h2>
    </x-slot>

    <div x-data="posApp()" class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full min-h-0" x-init="setPosMain($el); initPos()">
        <section class="lg:col-span-2 bg-white rounded shadow-sm p-4 h-full min-h-0 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="w-full max-w-md relative" @click.outside="closeTypeahead()">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <input
                            x-ref="topSearchInput"
                            x-model="typeahead.q"
                            @input="onTypeaheadInput()"
                            @keydown.enter.prevent="onTypeaheadEnter()"
                            @keydown.arrow-down.prevent="moveTypeahead(1)"
                            @keydown.arrow-up.prevent="moveTypeahead(-1)"
                            @keydown.escape.prevent="closeTypeahead()"
                            @focus="reopenTypeahead()"
                            placeholder="Scan or Search Product ID, name, or unit..."
                            class="placeholder-gray-400 bg-gray-100 border border-gray-200 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        />
                    </label>

                    <div
                        x-show="typeahead.open"
                        x-cloak
                        class="absolute left-0 right-0 z-50 mt-1 rounded border border-slate-200 bg-white shadow-lg"
                    >
                        <div class="max-h-72 overflow-auto text-sm">
                            <template x-if="typeahead.loading">
                                <div class="px-3 py-2 text-slate-500">Searching...</div>
                            </template>

                            <template x-if="!typeahead.loading && typeahead.items.length === 0">
                                <div class="px-3 py-2 text-slate-500">No matches found.</div>
                            </template>

                            <template x-for="(product, index) in typeahead.items" :key="product.id">
                                <button
                                    type="button"
                                    @mousedown.prevent="selectTypeaheadItem(index)"
                                    class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-b-0"
                                    :class="index === typeahead.activeIndex ? 'bg-slate-100' : 'hover:bg-slate-50'"
                                >
                                    <div class="font-medium text-slate-900">
                                        #<span x-text="product.id"></span> - <span x-text="product.name"></span>
                                    </div>
                                    <div class="text-xs text-slate-600">
                                        <span x-text="product.unit"></span>
                                        | P<span x-text="formatPrice(product.capital)"></span>
                                        <span x-show="product.available_quantity !== undefined">
                                            | Stock: <span x-text="formatQty(product.available_quantity)"></span>
                                        </span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <button @click="openBrowseModal" class="ml-4 inline-flex items-center gap-2 bg-white border rounded px-3 py-2 text-sm">
                    <span class="bg-indigo-900 text-white rounded w-5 h-5 flex items-center justify-center">+</span>
                    Browse Products
                </button>
            </div>

            <div class="border border-gray-200 rounded flex-1 min-h-0 flex flex-col overflow-hidden">
                <div class="px-4 py-3 bg-gray-100 border-b border-gray-200 font-medium">Current Order</div>
                <div class="flex-1 min-h-0 overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-600 bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3">Product ID</th>
                            <th class="px-4 py-3">Product Name</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Quantity</th>
                            <th class="px-4 py-3">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="item in order" :key="item.product_id">
                            <tr>
                                <td class="px-4 py-3" x-text="item.product_id"></td>
                                <td class="px-4 py-3" x-text="item.product_name"></td>
                                <td class="px-4 py-3" x-text="item.unit"></td>
                                <td class="px-4 py-3">P<span x-text="formatPrice(item.unit_price)"></span></td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        min="1"
                                        step="1"
                                        :value="item.quantity"
                                        :max="Math.max(1, getOrderMaxQty(item))"
                                        @input="onOrderQtyInput(item, $event.target)"
                                        @change="updateOrderQuantity(item.product_id, $event.target.value)"
                                        class="w-20 border rounded px-2 py-1 text-sm"
                                    />
                                    <div class="text-xs text-red-600 mt-1" x-show="Number(item.quantity) >= getOrderMaxQty(item)">
                                        Max: <span x-text="formatQty(getOrderMaxQty(item))"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">P<span x-text="formatPrice(item.subtotal)"></span></td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="removeOrderItem(item.product_id)" class="text-xs text-red-500">Remove</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="order.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                No products in order. Use "Browse Products" to add items.
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </section>

        <aside class="bg-white rounded shadow-sm p-6 h-full min-h-0 flex flex-col">
            <h3 class="text-2xl font-semibold mb-4 text-center">Summary</h3>

            <div class="border-t border-b py-2 mb-4">
                <div class="grid grid-cols-4 gap-2 text-sm text-gray-500 font-medium px-1">
                    <div>NAME</div>
                    <div>QTY</div>
                    <div>PRICE</div>
                    <div>TOTAL</div>
                </div>
            </div>

            <div class="space-y-2 overflow-y-auto flex-1 min-h-0 mb-6 pr-1">
                <template x-for="item in order" :key="item.product_id">
                    <div class="grid grid-cols-4 gap-2 items-center text-sm px-1">
                        <div class="text-gray-700" x-text="item.product_name"></div>
                        <div class="text-gray-700" x-text="formatQty(item.quantity)"></div>
                        <div class="text-gray-700">P<span x-text="formatPrice(item.unit_price)"></span></div>
                        <div>P<span x-text="formatPrice(item.subtotal)"></span></div>
                    </div>
                </template>
                <template x-if="order.length === 0">
                    <div class="text-gray-400 text-sm">No items in order</div>
                </template>
            </div>

            <div class="border-t pt-4">
                <div class="flex items-center justify-between mb-6">
                    <div class="text-sm text-gray-600">Total</div>
                    <div class="text-lg font-semibold">P<span x-text="formatPrice(total)"></span></div>
                </div>

                <button @click="openCheckoutModal" class="w-full bg-black text-white py-3 rounded mb-3 disabled:opacity-50" :disabled="order.length === 0 || checkout.processing">Checkout</button>
                <button @click="clearOrder" class="w-full border border-gray-300 py-3 rounded text-gray-600">Cancel Transaction</button>
            </div>
        </aside>

        <x-modal name="checkout-cash" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Checkout</h3>
                    <button @click="$dispatch('close-modal', 'checkout-cash')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Payment Method</span>
                        <span class="font-medium text-slate-900">Cash</span>
                    </div>
                    <div class="flex items-center justify-between border-t pt-3">
                        <span class="text-slate-600">Amount Due</span>
                        <span class="text-base font-semibold text-slate-900">P<span x-text="formatPrice(total)"></span></span>
                    </div>
                </div>

                <p x-show="checkout.error" class="mt-4 text-sm text-red-600" x-text="checkout.error"></p>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button @click="$dispatch('close-modal', 'checkout-cash')" class="px-4 py-2 border rounded text-sm text-slate-700">Cancel</button>
                    <button @click="confirmCheckout" :disabled="checkout.processing || order.length === 0" class="px-4 py-2 rounded bg-black text-white text-sm disabled:opacity-50">
                        <span x-show="!checkout.processing">Confirm Cash Payment</span>
                        <span x-show="checkout.processing">Processing...</span>
                    </button>
                </div>
            </div>
        </x-modal>

        <x-modal name="receipt-preview" maxWidth="md" focusable>
            <div class="p-6 text-sm text-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Receipt</h3>
                    <button @click="$dispatch('close-modal', 'receipt-preview')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
                </div>

                <template x-if="receipt">
                    <div id="receipt-content" class="space-y-3">
                        <div class="border-b pb-3">
                            <div><span class="text-slate-500">Sale ID:</span> <span class="font-medium" x-text="receipt.sale_id"></span></div>
                            <div><span class="text-slate-500">Date:</span> <span x-text="receipt.date"></span></div>
                            <div><span class="text-slate-500">Cashier:</span> <span x-text="receipt.cashier"></span></div>
                            <div><span class="text-slate-500">Branch:</span> <span x-text="receipt.branch_name || ('#' + receipt.branch_id)"></span></div>
                            <div><span class="text-slate-500">Terminal:</span> T<span x-text="receipt.terminal_id"></span> - <span x-text="receipt.terminal_name"></span></div>
                            <div><span class="text-slate-500">Payment:</span> <span class="uppercase" x-text="receipt.payment_method"></span></div>
                        </div>

                        <div>
                            <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-slate-500 mb-2">
                                <div class="col-span-5">Item</div>
                                <div class="col-span-2 text-right">Qty</div>
                                <div class="col-span-2 text-right">Price</div>
                                <div class="col-span-3 text-right">Subtotal</div>
                            </div>
                            <template x-for="item in (receipt.items || [])" :key="item.product_id">
                                <div class="grid grid-cols-12 gap-2 py-1 border-t border-slate-100">
                                    <div class="col-span-5">
                                        <div class="font-medium" x-text="item.product_name"></div>
                                        <div class="text-xs text-slate-500">#<span x-text="item.product_id"></span> · <span x-text="item.unit"></span></div>
                                    </div>
                                    <div class="col-span-2 text-right" x-text="formatQty(item.quantity)"></div>
                                    <div class="col-span-2 text-right">P<span x-text="formatPrice(item.unit_price)"></span></div>
                                    <div class="col-span-3 text-right">P<span x-text="formatPrice(item.subtotal)"></span></div>
                                </div>
                            </template>
                        </div>

                        <div class="border-t pt-3 flex items-center justify-between text-base">
                            <span class="font-medium">Total</span>
                            <span class="font-semibold">P<span x-text="formatPrice(receipt.total)"></span></span>
                        </div>
                    </div>
                </template>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button @click="printReceipt" :disabled="!receipt" class="px-4 py-2 border rounded text-sm text-slate-700 disabled:opacity-50">Print</button>
                    <button @click="$dispatch('close-modal', 'receipt-preview')" class="px-4 py-2 rounded bg-black text-white text-sm">Done</button>
                </div>
            </div>
        </x-modal>

        <x-modal name="browse-products" maxWidth="2xl" focusable>
            <div class="p-6" x-on:keydown.escape.window="$dispatch('close-modal', 'browse-products')">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="text-xl font-semibold text-slate-900">Browse Products</h3>
                    <button @click="$dispatch('close-modal', 'browse-products')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
                </div>

                <div class="mb-4">
                    <input
                        x-model.debounce.300ms="browse.q"
                        @input="fetchBrowseProducts(1)"
                        placeholder="Search product name or unit"
                        class="w-full border border-slate-300 rounded px-3 py-2 text-sm"
                    />
                    <p x-show="requestError" class="mt-2 text-sm text-red-600" x-text="requestError"></p>
                </div>

                <div class="border rounded-lg overflow-hidden">
                    <div class="max-h-96 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100 text-slate-700 text-left">
                                <tr>
                                    <th class="px-3 py-2">ID</th>
                                    <th class="px-3 py-2">Name</th>
                                    <th class="px-3 py-2">Unit</th>
                                    <th class="px-3 py-2">Price</th>
                                    <th class="px-3 py-2">Stock</th>
                                    <th class="px-3 py-2">Qty</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="product in browse.products" :key="product.id">
                                    <tr>
                                        <td class="px-3 py-2" x-text="product.id"></td>
                                        <td class="px-3 py-2" x-text="product.name"></td>
                                        <td class="px-3 py-2" x-text="product.unit"></td>
                                        <td class="px-3 py-2">P<span x-text="formatPrice(product.capital)"></span></td>
                                        <td class="px-3 py-2">
                                            <span x-text="formatQty(product.available_quantity ?? 0)"></span>
                                            <span class="text-xs text-slate-500" x-show="getOrderQty(product.id) > 0">
                                                (in cart: <span x-text="formatQty(getOrderQty(product.id))"></span>)
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                x-model.number="browse.qty[product.id]"
                                                @input="onBrowseQtyInput(product)"
                                                :max="Math.max(1, getRemainingStock(product))"
                                                class="w-16 border rounded px-2 py-1"
                                            />
                                            <div class="text-xs text-red-600 mt-1" x-show="getRequestedQty(product) > getRemainingStock(product)">
                                                Max addable: <span x-text="formatQty(getRemainingStock(product))"></span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button
                                                @click="addProductToCart(product)"
                                                :disabled="!canAddToCart(product)"
                                                class="px-3 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Add
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!browse.loading && browse.products.length === 0">
                                    <td colspan="7" class="px-3 py-8 text-center text-slate-500">No products found.</td>
                                </tr>
                                <tr x-show="browse.loading">
                                    <td colspan="7" class="px-3 py-8 text-center text-slate-500">Loading products...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between px-4 py-3 border-t bg-slate-50 text-sm">
                        <div>
                            Showing page <span x-text="browse.pagination.current_page"></span>
                            of <span x-text="browse.pagination.last_page"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="setBrowsePage(browse.pagination.current_page - 1)"
                                :disabled="browse.pagination.current_page <= 1"
                                class="px-3 py-1 border rounded disabled:opacity-50"
                            >
                                Prev
                            </button>
                            <button
                                @click="setBrowsePage(browse.pagination.current_page + 1)"
                                :disabled="browse.pagination.current_page >= browse.pagination.last_page"
                                class="px-3 py-1 border rounded disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>

<script>
function setPosMain(el) {
    const setMain = () => {
        const h = document.querySelector('header')?.getBoundingClientRect().height || 0;
        el.style.height = `calc(100vh - ${h}px)`;
        document.documentElement.style.overflowY = 'hidden';
    };

    setMain();
    window.addEventListener('resize', setMain);
}

function posApp() {
    return {
        order: [],
        total: 0,
        requestError: '',
        checkout: {
            processing: false,
            error: '',
        },
        receipt: null,
        typeahead: {
            q: '',
            items: [],
            open: false,
            loading: false,
            activeIndex: -1,
            debounceHandle: null,
            limit: 8,
        },
        browse: {
            q: '',
            products: [],
            qty: {},
            loading: false,
            perPage: 10,
            pagination: {
                current_page: 1,
                last_page: 1,
            },
        },

        async initPos() {
            await this.refreshOrder();
        },

        async openBrowseModal() {
            this.browse.q = '';
            this.$dispatch('open-modal', 'browse-products');
            await this.fetchBrowseProducts(1);
        },

        openCheckoutModal() {
            this.checkout.error = '';
            this.$dispatch('open-modal', 'checkout-cash');
        },

        async confirmCheckout() {
            if (this.checkout.processing || this.order.length === 0) {
                return;
            }

            this.checkout.processing = true;
            this.checkout.error = '';

            try {
                const result = await this.postJson(`{{ route('pos.api.checkout.finalize') }}`, {
                    payment_method: 'cash',
                });

                this.$dispatch('close-modal', 'checkout-cash');
                await this.refreshOrder();
                this.receipt = result.receipt ?? null;
                if (this.receipt) {
                    this.$dispatch('open-modal', 'receipt-preview');
                }
            } catch (error) {
                this.checkout.error = error instanceof Error ? error.message : 'Checkout failed.';
                this.setRequestError(error);
            } finally {
                this.checkout.processing = false;
            }
        },

        printReceipt() {
            if (!this.receipt) {
                return;
            }

            const rows = (this.receipt.items || []).map((item) => {
                return `
                    <tr>
                        <td style="padding:4px 0;">${this.escapeHtml(item.product_name)}<br><small>#${item.product_id} · ${this.escapeHtml(item.unit ?? '')}</small></td>
                        <td style="padding:4px 0; text-align:right;">${this.formatQty(item.quantity)}</td>
                        <td style="padding:4px 0; text-align:right;">${this.formatPrice(item.unit_price)}</td>
                        <td style="padding:4px 0; text-align:right;">${this.formatPrice(item.subtotal)}</td>
                    </tr>
                `;
            }).join('');

            const html = `
                <!doctype html>
                <html>
                <head>
                    <meta charset="utf-8" />
                    <title>Receipt #${this.receipt.sale_id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 16px; color: #111; }
                        h1 { font-size: 18px; margin: 0 0 8px; }
                        .meta { font-size: 12px; margin-bottom: 10px; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; }
                        th { text-align: left; border-bottom: 1px solid #ddd; padding: 6px 0; }
                        td { border-bottom: 1px solid #f0f0f0; }
                        .right { text-align: right; }
                        .total { margin-top: 10px; text-align: right; font-weight: 700; }
                    </style>
                </head>
                <body>
                    <h1>Sales Receipt</h1>
                    <div class="meta">
                        <div>Sale ID: ${this.receipt.sale_id}</div>
                        <div>Date: ${this.escapeHtml(this.receipt.date ?? '')}</div>
                        <div>Cashier: ${this.escapeHtml(this.receipt.cashier ?? '')}</div>
                        <div>Branch: ${this.escapeHtml(this.receipt.branch_name ?? ('#' + this.receipt.branch_id))}</div>
                        <div>Terminal: ${this.receipt.terminal_id} - ${this.escapeHtml(this.receipt.terminal_name ?? '')}</div>
                        <div>Payment: ${this.escapeHtml((this.receipt.payment_method ?? 'cash').toUpperCase())}</div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="right">Qty</th>
                                <th class="right">Price</th>
                                <th class="right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                    <div class="total">Total: P${this.formatPrice(this.receipt.total)}</div>
                    <script>window.print();window.close();<\/script>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank', 'width=420,height=700');
            if (!printWindow) {
                this.setRequestError(new Error('Unable to open print window. Please allow popups for this site.'));
                return;
            }

            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
        },

        escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        },

        async searchFromTop() {
            this.browse.q = this.typeahead.q.trim();
            this.$dispatch('open-modal', 'browse-products');
            await this.fetchBrowseProducts(1);
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

            const params = new URLSearchParams({ q: query, limit: String(this.typeahead.limit) });

            try {
                const data = await this.getJson(`{{ route('pos.api.products.search') }}?${params.toString()}`);

                this.typeahead.items = Array.isArray(data) ? data : [];
                this.typeahead.activeIndex = this.typeahead.items.length > 0 ? 0 : -1;
            } catch (error) {
                this.typeahead.items = [];
                this.typeahead.activeIndex = -1;
                this.setRequestError(error);
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

        async onTypeaheadEnter() {
            if (this.typeahead.open && this.typeahead.items.length > 0) {
                const index = this.typeahead.activeIndex >= 0 ? this.typeahead.activeIndex : 0;
                await this.selectTypeaheadItem(index);
                return;
            }

            await this.searchFromTop();
        },

        async selectTypeaheadItem(index) {
            const product = this.typeahead.items[index];
            if (!product) {
                return;
            }

            this.browse.qty[product.id] = this.normalizeQty(this.browse.qty[product.id] ?? 1, 1);
            await this.addProductToCart(product);

            this.typeahead.q = '';
            this.typeahead.items = [];
            this.closeTypeahead();

            this.$nextTick(() => {
                this.$refs.topSearchInput?.focus();
            });
        },

        async fetchBrowseProducts(page = 1) {
            this.browse.loading = true;

            const params = new URLSearchParams({
                page: String(page),
                per_page: String(this.browse.perPage),
            });

            if (this.browse.q) {
                params.set('q', this.browse.q);
            }


            try {
                const data = await this.getJson(`{{ route('pos.api.products.browse') }}?${params.toString()}`);

                this.browse.products = data.data ?? [];
                this.browse.pagination.current_page = data.current_page ?? 1;
                this.browse.pagination.last_page = data.last_page ?? 1;

                for (const product of this.browse.products) {
                    if (!this.browse.qty[product.id]) {
                        this.browse.qty[product.id] = 1;
                    }
                }
            } catch (error) {
                this.browse.products = [];
                this.browse.pagination.current_page = 1;
                this.browse.pagination.last_page = 1;
                this.setRequestError(error);
            } finally {
                this.browse.loading = false;
            }
        },

        async setBrowsePage(page) {
            if (page < 1 || page > this.browse.pagination.last_page) {
                return;
            }

            await this.fetchBrowseProducts(page);
        },

        async addProductToCart(product) {
            const qty = this.normalizeQty(this.browse.qty[product.id] ?? 1, 1);
            const remaining = this.getRemainingStock(product);

            if (remaining < 1) {
                this.setRequestError(new Error('No remaining stock for this product in the selected branch.'));
                return;
            }

            if (qty > remaining) {
                this.browse.qty[product.id] = remaining;
                this.setRequestError(new Error(`Requested quantity exceeds stock. Max addable is ${remaining}.`));
                return;
            }

            this.browse.qty[product.id] = qty;
            this.requestError = '';

            await this.postJson(`{{ route('pos.api.cart.add') }}`, {
                product_id: product.id,
                quantity: qty,
            });

            await this.refreshOrder();
        },

        async updateOrderQuantity(productId, quantity) {
            const qty = this.normalizeQty(quantity, 1);
            if (qty < 1) {
                return;
            }

            const item = this.order.find((entry) => entry.product_id === productId);
            if (item && qty > this.getOrderMaxQty(item)) {
                this.setRequestError(new Error(`Requested quantity exceeds stock. Max allowed is ${this.getOrderMaxQty(item)}.`));
                await this.refreshOrder();
                return;
            }

            this.requestError = '';

            await this.postJson(`{{ route('pos.api.cart.update') }}`, {
                product_id: productId,
                quantity: qty,
            });

            await this.refreshOrder();
        },

        async removeOrderItem(productId) {
            await this.postJson(`{{ route('pos.api.cart.remove') }}`, {
                product_id: productId,
            });

            await this.refreshOrder();
        },

        async clearOrder() {
            const ids = this.order.map((item) => item.product_id);

            for (const id of ids) {
                await this.removeOrderItem(id);
            }
        },

        async refreshOrder() {
            try {
                const data = await this.getJson(`{{ route('pos.api.checkout.prepare') }}`);

                this.order = data.items ?? [];
                this.total = data.total ?? 0;
            } catch (error) {
                this.order = [];
                this.total = 0;
                this.setRequestError(error);
            }
        },

        getOrderQty(productId) {
            const item = this.order.find((entry) => entry.product_id === productId);

            return item ? Number(item.quantity) : 0;
        },

        getOrderMaxQty(item) {
            return Math.max(1, Math.floor(Number(item?.available_quantity ?? 0)));
        },

        onOrderQtyInput(item, inputEl) {
            const max = this.getOrderMaxQty(item);
            const parsed = this.normalizeQty(inputEl?.value, 1);

            if (parsed > max) {
                inputEl.value = String(max);
            }
        },

        getAvailableStock(product) {
            return Math.max(0, Math.floor(Number(product?.available_quantity ?? 0)));
        },

        getRemainingStock(product) {
            return Math.max(0, this.getAvailableStock(product) - this.getOrderQty(product.id));
        },

        getRequestedQty(product) {
            return this.normalizeQty(this.browse.qty[product.id] ?? 1, 1);
        },

        canAddToCart(product) {
            const remaining = this.getRemainingStock(product);
            const requested = this.getRequestedQty(product);

            return remaining > 0 && requested <= remaining;
        },

        onBrowseQtyInput(product) {
            const requested = this.getRequestedQty(product);
            const remaining = this.getRemainingStock(product);

            if (remaining > 0 && requested > remaining) {
                this.browse.qty[product.id] = remaining;
            }
        },

        formatPrice(value) {
            return Number(value ?? 0).toFixed(2);
        },

        formatQty(value) {
            return String(Math.max(0, Math.floor(Number(value ?? 0))));
        },

        normalizeQty(value, min = 0) {
            const parsed = parseInt(value, 10);
            if (!Number.isFinite(parsed)) {
                return min;
            }

            return Math.max(min, parsed);
        },

        async getJson(url) {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return this.parseJsonResponse(response);
        },

        async parseJsonResponse(response) {
            const text = await response.text();
            const contentType = response.headers.get('content-type') || '';

            if (!response.ok) {
                throw new Error(this.extractErrorMessage(text, response.status));
            }

            if (!contentType.includes('application/json')) {
                throw new Error('Unexpected server response. Please refresh and try again.');
            }

            try {
                return JSON.parse(text);
            } catch {
                throw new Error('Failed to read server response. Please try again.');
            }
        },

        extractErrorMessage(text, status) {
            if (status === 401 || status === 419) {
                return 'Session expired. Please refresh and sign in again.';
            }

            if (status === 403) {
                return 'You do not have permission to perform this action.';
            }

            try {
                const payload = JSON.parse(text);
                if (payload.message) {
                    return payload.message;
                }
            } catch {
                // Ignore parse errors and fall back to generic messages.
            }

            return `Request failed (${status}).`;
        },

        setRequestError(error) {
            this.requestError = error instanceof Error ? error.message : 'Something went wrong.';
            console.error(this.requestError);
        },

        async postJson(url, payload) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            return this.parseJsonResponse(response);
        },
    };
}
</script>

