<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">New User</h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="w-full pb-8">
        <x-card title="Add New User" class="w-full">
            @can('users.create')
                <form method="POST" action="{{ route('users.store') }}"
                    x-ref="userForm"
                    novalidate
                    x-data="{
                      // Initialize form with old data if a server validation fails
                      form: {
                          name: @js(old('name', '')),
                          address: @js(old('address', '')),
                          phone: @js(old('phone', '')),
                          role: @js(old('role', '')),
                          branch_id: @js(old('branch_id', '')),
                          pin: '',
                          pin_confirmation: ''
                      },
                      // Seed with Laravel's backend errors automatically
                      errors: @js($errors->getMessages()),
                      showConfirmModal: false,
                      
                      handleFormSubmit() {
                          this.errors = {};
                          let isValid = true;

                          // Validate fields and wrap messages in arrays to match Laravel's format for your component
                          if (!this.form.name.trim()) { this.errors.name = ['Full name is required.']; isValid = false; }
                          if (!this.form.address.trim()) { this.errors.address = ['Address is required.']; isValid = false; }
                          if (!this.form.phone.trim()) { this.errors.phone = ['Contact number is required.']; isValid = false; }
                          if (!this.form.role) { this.errors.role = ['Role is required.']; isValid = false; }
                          if (!this.form.branch_id) { this.errors.branch_id = ['Branch is required.']; isValid = false; }
                          
                          if (!this.form.pin) { 
                              this.errors.pin = ['PIN is required.']; 
                              isValid = false; 
                          }
                          
                          if (!this.form.pin_confirmation) { 
                              this.errors.pin_confirmation = ['Please confirm your PIN.']; 
                              isValid = false; 
                          } else if (this.form.pin !== this.form.pin_confirmation) {
                              this.errors.pin_confirmation = ['PINs do not match.']; 
                              isValid = false;
                          }

                          // Show modal only if no errors were found
                          if (isValid) {
                              this.showConfirmModal = true;
                          }
                      },

                      confirmAndSubmit() {
                          this.$refs.userForm.submit();
                      }
                  }"
                >
                    @csrf

                    <div class="space-y-6">

                        {{-- Basic Info (Full Width) --}}
                        <div class="rounded border border-slate-200 p-6 space-y-5 bg-white">
                            <div class="flex items-center gap-2 border-b border-slate-100">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Basic info</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="sm:col-span-2">
                                    <x-forms.input name="name" label="Full name" model="form.name" :required="true" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-forms.input name="address" label="Address" model="form.address" :required="true" />
                                </div>

                                <div class="sm:col-span-1">
                                    <!-- Assuming phone-input accepts the same props as your standard input -->
                                    <x-forms.phone-input name="phone" label="Contact number" model="form.phone" :required="true" />
                                </div>
                            </div>
                        </div>

                        {{-- Two-Column Layout for Role & PIN --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                            {{-- Role & Access (Left Column) --}}
                            <div class="rounded border border-slate-200 p-6 space-y-5 bg-white">
                                <div class="flex items-center gap-2 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Role & access</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <!-- Select tags styled manually to match your component's reactive design -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Role <span class="text-red-500">*</span></label>
                                        <select name="role" x-model="form.role"
                                            :class="errors?.role ? 'border-red-500 text-red-900 focus:border-red-600 focus:ring-1 focus:ring-red-600' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'"
                                            class="mt-1 block w-full rounded border py-2 pl-4 pr-10 text-sm focus:outline-none shadow-sm transition-colors"
                                        >
                                            <option value="">-- Select role --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <template x-if="errors?.role">
                                            <p class="mt-1 text-sm text-red-600" x-text="errors.role[0]"></p>
                                        </template>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Branch <span class="text-red-500">*</span></label>
                                        <select name="branch_id" x-model="form.branch_id"
                                            :class="errors?.branch_id ? 'border-red-500 text-red-900 focus:border-red-600 focus:ring-1 focus:ring-red-600' : 'border-slate-300 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'"
                                            class="mt-1 block w-full rounded border py-2 pl-4 pr-10 text-sm focus:outline-none shadow-sm transition-colors"
                                        >
                                            <option value="">-- Select branch --</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        <template x-if="errors?.branch_id">
                                            <p class="mt-1 text-sm text-red-600" x-text="errors.branch_id[0]"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- PIN (Right Column) --}}
                            <div class="rounded border border-slate-200 p-6 space-y-5 bg-white flex flex-col">
                                <div class="flex items-center gap-2 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">PIN</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 flex-1">
                                    <div>
                                        <x-forms.input name="pin" type="password" label="PIN" model="form.pin" :required="true" />
                                    </div>

                                    <div>
                                        <x-forms.input name="pin_confirmation" type="password" label="Confirm PIN" model="form.pin_confirmation" :required="true" />
                                    </div>
                                </div>

                                <p class="text-xs text-slate-500 pt-1 mt-auto">The user will use this PIN to log in to the system.</p>
                            </div>

                        </div> 

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a
                                href="{{ route('users.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded text-sm font-medium text-slate-700 hover:bg-slate-50 transition ease-in-out duration-150 shadow-sm"
                            >
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button type="button" @click="handleFormSubmit()" class="px-4 py-2">
                                {{ __('Create user') }}
                            </x-primary-button>
                        </div>

                    </div>

                    <!-- Confirmation Modal -->
                    <div
                        x-show="showConfirmModal"
                        x-transition
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                        @click.self="showConfirmModal = false"
                    >
                        <div class="w-full max-w-sm rounded bg-white p-6 shadow-lg">
                            <h3 class="mb-2 text-lg font-semibold text-slate-900">Confirm New User</h3>
                            <p class="mb-6 text-sm text-slate-600">
                                Are you sure you want to create this user? Ensure the assigned role and branch are correct.
                            </p>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="showConfirmModal = false"
                                    class="flex-1 rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Review Form
                                </button>
                                <button
                                    type="button"
                                    @click="confirmAndSubmit()"
                                    class="flex-1 rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Yes, Create User
                                </button>
                            </div>
                        </div>
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
    </div>
</x-app-layout>