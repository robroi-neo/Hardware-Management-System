<!-- Create Supplier Modal -->
<div id="createSupplier" class="hidden fixed inset-0 flex items-center justify-center z-50">

    <!-- Backdrop -->
    <div onclick="toggleModal(false)" class="absolute inset-0 bg-gray-500/75"></div>

    <!-- Popup Form -->
    <form id="supplier-create" method="POST" action="{{ route('supplier.store') }}"
        class="relative bg-white p-6 rounded-2xl shadow-lg z-10 space-y-4 w-135">
        @csrf

        <h2 class="text-xl font-semibold mb-4 text-center">Create New Supplier</h2>

        <!-- Supplier Name -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Supplier Name:</label>
            <input name="supplier_name" type="text"
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none"
                required>
        </div>

        <!-- Supplier Address -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Supplier Address:</label>
            <input name="address" type="text"
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none"
                required>
        </div>

        <!-- Contact Person -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Contact Person:</label>
            <input name="contact_person" type="text"
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none"
                required>
        </div>

        <!-- Contact Number -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Contact Number:</label>
            <input name="contact_number" type="text"
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none"
                required>
        </div>

        <div class="flex">
            <p class="text-neutral-400 italic text-sm">Please ensure that the supplier details are correct before
                submission.</p>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-3 mt-4">
            <button type="button" onclick="toggleModal(false)"
                class="px-4 py-2 bg-neutral-200 text-neutral-900 rounded-md hover:bg-neutral-300">
                Cancel
            </button>
            <button type="submit" onclick="toggleMOdal()"
                class="px-4 py-2 bg-neutral-900 text-white rounded-md hover:bg-neutral-700">
                Confirm
            </button>
        </div>
    </form>
</div>

<script>
    function toggleModal(show) {
        clearSupplierForm();
        document.getElementById("createSupplier").classList.toggle("hidden", !show);
    }

    function clearSupplierForm() {
        const form = document.querySelector('#supplier-create');
        if (form) form.reset();
    }

    function confirmCreate() {
        document.getElementById("supplier-create").requestSubmit();
    }
</script>
