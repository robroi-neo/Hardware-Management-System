<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">New User</h2>
    </x-slot>

    <x-card class="w-full h-auto mb-0 p-4 flex flex-col">
        @can('users.create')
            <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                @csrf

                {{-- Basic Info --}}
                <div class="rounded border border-slate-200 p-6 bg-white">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Basic info</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="name" :value="__('Full name')" />
                            <x-text-input id="name" class="block mt-1 w-full text-sm" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" class="block mt-1 w-full text-sm" type="text" name="address" :value="old('address')" required autocomplete="address" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-1">
                            <x-input-label for="phone" :value="__('Contact number')" />
                            <x-text-input id="phone" class="block mt-1 w-full text-sm" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="+63 9XX XXX XXXX" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Role & Access --}}
                <div class="rounded border border-slate-200 p-6 bg-white">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Role & access</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" required class="block mt-1 w-full rounded border border-slate-300 py-2 pl-4 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Select role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="branch" :value="__('Branch')" />
                            <select id="branch" name="branch_id" required class="block mt-1 w-full rounded border border-slate-300 py-2 pl-4 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Select branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('branch')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- PIN --}}
                <div class="rounded border border-slate-200 p-6 bg-white">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">PIN Code</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="pin" :value="__('PIN')" />
                            <x-text-input id="pin" class="block mt-1 w-full text-sm" type="password" name="pin" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('pin')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="pin_confirmation" :value="__('Confirm PIN')" />
                            <x-text-input id="pin_confirmation" class="block mt-1 w-full text-sm" type="password" name="pin_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('pin_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 pt-1">The user will use this PIN to log in to the system.</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded text-sm font-medium text-slate-700 hover:bg-slate-50 transition ease-in-out duration-150 shadow-sm"
                    >
                        {{ __('Cancel') }}
                    </a>

                    <x-primary-button class="px-4 py-2 text-sm shadow-sm">
                        {{ __('Create user') }}
                    </x-primary-button>
                </div>
            </form>
        @else
            <div class="p-4 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                <p class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                    </svg>
                    You don't have permission to create users.
                </p>
            </div>
        @endcan
    </x-card>
</x-app-layout>