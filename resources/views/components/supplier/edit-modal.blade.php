<!-- Edit Supplier Modal -->
<div id="editSupplierModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
    <!-- Backdrop -->
    <div onclick="toggleEditModal(false)" class="absolute inset-0 bg-gray-500/75"></div>

    <!-- Popup Form -->
    <!-- add edit action later -->
    <form method="POST" id="editSupplierForm" class="relative bg-white p-6 rounded-2xl shadow-lg z-10 space-y-4 w-135"
        action="">
        {{-- empty on render --}}
        @csrf
        @method('PATCH')
        <h2 class="text-xl font-semibold mb-4 text-center">Edit Supplier Details</h2>

        <!-- Supplier ID (read-only) -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Supplier ID:</label>
            <input id="id" name="id" type="text" readonly class="p-2 bg-white text-neutral-700" required
                disabled />
        </div>

        <!-- edit Name -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Supplier name:</label>
            <input id="edit_name" name="supplier_name" type="text" required
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none" />
        </div>

        <!-- edit Address -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Supplier Address:</label>
            <input id="edit_address" name="address" type="text" required
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none" />
        </div>

        <!-- edit Contact Person -->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Contact Person:</label>
            <input id="edit_person" name="contact_person" type="text" required
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none" />
        </div>

        <!-- edit Contact Number-->
        <div class="grid grid-cols-2 gap-1 items-center">
            <label class="font-semibold text-gray-700">Contact Number:</label>
            <input id="edit_contact" name="contact_number" type="text" required
                class="border border-neutral-300 rounded-md p-2 focus:ring-2 focus:ring-neutral-500 focus:outline-none" />
        </div>
        <div class="flex">
            <p class="text-neutral-400 italic text-sm"> Please ensure that the item details are correct.</p>
        </div>

        <!-- Footer -->
        <!-- Footer -->
        <div class="flex justify-end space-x-3 mt-4">
            <button type="button" onclick="toggleEditModal(false)"
                class="px-4 py-2 bg-neutral-200 text-neutral-900 rounded-md hover:bg-neutral-300">
                Cancel
            </button>
            <button type="button" onclick="confirmEdit()"
                class="px-4 py-2 bg-neutral-900 text-white rounded-md hover:bg-neutral-700">
                Save Changes
            </button>
        </div>
    </form>
</div>



<script>
    // base URL to append id to:
    const supplierBaseUrl = "{{ url('/supplier') }}"; // -> "/supplier"

    function editRow(values) {
        console.log(values.id);
        document.getElementById('id').value = values.id;
        document.getElementById('edit_name').value = values.supplier_name ?? '';
        document.getElementById('edit_address').value = values.address ?? '';
        document.getElementById('edit_person').value = values.contact_person ?? '';
        document.getElementById('edit_contact').value = values.contact_number ?? '';

        // set the form action to /supplier/{id}
        document.getElementById('editSupplierForm').action = supplierBaseUrl + '/' + values.id;

        toggleEditModal(true);
    }

    function toggleEditModal(show) {
        const modal = document.getElementById("editSupplierModal");
        modal.classList.toggle("hidden", !show);
    }

    function confirmEdit() {

        let validity = document.getElementById("editSupplierForm").checkValidity();
        if (!validity) {
            document.getElementById("editSupplierForm").requestSubmit();
            return
        }
        const confirmation = confirm(
            "Editing this supplier will permanently modify their details in the system. This cannot be undone.\n\nDo you wish to continue?"
        );
        if (confirmation) {
            document.getElementById("editSupplierForm").requestSubmit();
        }
    }
</script>
