<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">New User</h2>
    </x-slot>

    <x-card class="max-w-2xl">
        @can('users.create')
            <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input
                        id="name"
                        class="block mt-1 w-full"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Phone -->
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input
                        id="phone"
                        class="block mt-1 w-full"
                        type="tel"
                        name="phone"
                        :value="old('phone')"
                        required
                        autocomplete="tel"
                    />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <!-- Role -->
                <div>
                    <x-input-label for="role" :value="__('Role')" />
                    <select
                        id="role"
                        name="role"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="">-- Select Role --</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="cashier" {{ old('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <!-- PIN -->
                <div>
                    <x-input-label for="pin" :value="__('PIN')" />
                    <x-text-input
                        id="pin"
                        class="block mt-1 w-full"
                        type="password"
                        name="pin"
                        required
                        autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('pin')" class="mt-2" />
                </div>

                <!-- Confirm PIN -->
                <div>
                    <x-input-label for="pin_confirmation" :value="__('Confirm PIN')" />
                    <x-text-input
                        id="pin_confirmation"
                        class="block mt-1 w-full"
                        type="password"
                        name="pin_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('pin_confirmation')" class="mt-2" />
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150"
                    >
                        {{ __('Cancel') }}
                    </a>

                    <x-primary-button>
                        {{ __('Create User') }}
                    </x-primary-button>
                </div>
            </form>
        @else
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <p>You don't have permission to create users.</p>
            </div>
        @endcan
    </x-card>
</x-app-layout>
