<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">New User</h2>
    </x-slot>

    <x-card class="max-w-2xl">
        @can('users.create')
            <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                @csrf

                {{-- Basic Info --}}
                <div class="rounded-xl border border-gray-200 p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Basic info</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-input-label for="name" :value="__('Full name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" required autocomplete="address" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Contact number')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="+63 9XX XXX XXXX" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Role & Access --}}
                <div class="rounded-xl border border-gray-200 p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Role & access</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" required class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
                            <select id="branch" name="branch_id" required class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
                <div class="rounded-xl border border-gray-200 p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">PIN</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="pin" :value="__('PIN')" />
                            <x-text-input id="pin" class="block mt-1 w-full" type="password" name="pin" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('pin')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="pin_confirmation" :value="__('Confirm PIN')" />
                            <x-text-input id="pin_confirmation" class="block mt-1 w-full" type="password" name="pin_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('pin_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <p class="text-xs text-gray-400">The user will use this PIN to log in to the system.</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150"
                    >
                        {{ __('Cancel') }}
                    </a>

                    <x-primary-button>
                        {{ __('Create user') }}
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
