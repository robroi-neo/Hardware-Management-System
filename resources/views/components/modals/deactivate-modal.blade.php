{{-- resources/views/components/modal/delete-confirmation.blade.php --}}

@props([
    'title' => 'Deactivate Item',
    'message' => 'Are you sure you want to deactuvate this item?',
    'action' => '',
    'itemName' => 'deactivateItemName',
])

<div
    x-show="showDeactivateModal"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="closeDeactivateModal()"
>
    <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
        <h3 class="mb-2 text-lg font-semibold text-slate-900">
            {{ $title }}
        </h3>

        <p class="mb-6 text-sm text-slate-600">
            {{ $message }}
            <strong x-text="{{ $itemName }}"></strong>?
            Do you want to proceed with deactivation?
        </p>

        <form
            :action="{{ $action }}"
            method="POST"
            class="flex gap-3"
        >
            @csrf
            @method('DELETE')

            <button
                type="button"
                @click="closeDeactiateModal()"
                class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                type="submit"
                class="flex-1 rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
            >
                Deactivate
            </button>
        </form>
    </div>
</div>
