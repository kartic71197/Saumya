<div>
    <div class="py-12">
        <div class="max-w-screen-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="w-full">
                    <section class="w-full">
                        <header
                            class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Manage Field Representatives') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Review and manage field representatives linked to suppliers.') }}
                                </p>
                            </div>

                            <div>
                                <x-primary-button
                                    class="flex justify-center items-center min-w-44 px-4 py-2 bg-primary-md dark:bg-primary-md border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 hover:bg-primary-lt dark:hover:bg-primary-lt focus:bg-primary-dk dark:focus:bg-primary-dk active:bg-primary-dk dark:active:bg-primary-dk focus:outline-none focus:ring-2 focus:ring-primary-md focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    x-data="{ loading: false }"
                                    x-on:click="loading = true; $wire.openAddFieldRepModal(); setTimeout(() => loading = false, 800)"
                                    x-bind:disabled="loading">

                                    <span x-show="!loading">{{ __('+ Add Field Rep') }}</span>

                                    <span x-show="loading" class="flex justify-center items-center w-full">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
                                        </svg>
                                    </span>
                                </x-primary-button>
                            </div>
                        </header>
                    </section>
                </div>
            </div>
        </div>
    </div>

    {{-- PowerGrid Table --}}
    <div class="max-w-screen-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div
                class="p-6 bg-white dark:bg-gray-800 border-b border-gray-600 dark:border-gray-700 text-sm dark:text-gray-400">
                <livewire:tables.field-reps-list />
            </div>
        </div>
    </div>

    {{-- ================= ADD MODAL ================= --}}
    <x-modal name="add-field-rep-modal" width="w-100" height="h-auto" maxWidth="3xl">
        <header class="p-3">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Add Field Representative') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Create a new field representative and link them to a supplier.') }}
            </p>
        </header>

        <form wire:submit.prevent="createFieldRep">
            <div class="space-y-12 p-3">
                <div class="border-b border-gray-900/10 pb-12">
                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                        {{-- Organization --}}
                        <div class="sm:col-span-3">
                            <x-input-label value="*Practices" />
                            <select wire:model="organization_id"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Practices</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Supplier --}}
                        <div class="sm:col-span-3">
                            <x-input-label value="*Supplier" />
                            <select wire:model="supplier_id"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Name --}}
                        <div class="sm:col-span-2">
                            <x-input-label value="*Name" />
                            <x-text-input wire:model="medrep_name" class="mt-1 block w-full" required />
                            @error('medrep_name') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="sm:col-span-2">
                            <x-input-label value="Phone" />
                            <x-text-input wire:model.lazy="medrep_phone" type="tel" maxlength="10"
                                placeholder="123-456-7890" onblur="formatPhone(event)" class="mt-1 block w-full" />
                            @error('medrep_phone') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>


                        {{-- Email --}}
                        <div class="sm:col-span-2">
                            <x-input-label value="Email" />
                            <x-text-input wire:model="medrep_email" type="email" class="mt-1 block w-full" />
                        </div>

                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-6" x-data="{ loading: false }">
                <x-primary-button x-on:click="loading = true; $wire.createFieldRep().then(() => loading = false)"
                    x-bind:disabled="loading">
                    <span x-show="!loading">{{ __('Create') }}</span>
                    <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white rounded-full"></span>
                </x-primary-button>

                <x-secondary-button x-on:click="$dispatch('close-modal', 'add-field-rep-modal')">
                    {{ __('Cancel') }}
                </x-secondary-button>
            </div>
        </form>
    </x-modal>

    {{-- ================= EDIT MODAL ================= --}}
    <x-modal name="edit-field-rep-modal" width="w-100" height="h-auto" maxWidth="3xl">
        <header class="p-3">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Edit Field Representative') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Update field representative details below.') }}
            </p>
        </header>

        <form wire:submit.prevent="updateFieldRep">
            <div class="space-y-12 p-3">
                <div class="border-b border-gray-900/10 pb-12">
                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                        {{-- Organization --}}
                        <div class="sm:col-span-3">
                            <x-input-label value="*Practices" />
                            <select wire:model="organization_id"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Practices</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Supplier --}}
                        <div class="sm:col-span-3">
                            <x-input-label value="*Supplier" />
                            <select wire:model="supplier_id"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Name --}}
                        <div class="sm:col-span-2">
                            <x-input-label value="*Name" />
                            <x-text-input wire:model="medrep_name" class="mt-1 block w-full" required />
                            @error('medrep_name') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="sm:col-span-2">
                            <x-input-label value="Phone" />
                            <x-text-input wire:model.lazy="medrep_phone" type="tel" maxlength="10"
                                placeholder="123-456-7890" onblur="formatPhone(event)" class="mt-1 block w-full" />
                            @error('medrep_phone') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="sm:col-span-2">
                            <x-input-label value="Email" />
                            <x-text-input wire:model="medrep_email" type="email" class="mt-1 block w-full" />
                            @error('medrep_email') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="flex justify-between gap-4 mt-6" x-data="{ loading: false }">
            <!-- LEFT: Delete Button -->    
            <x-danger-button wire:click="deleteFieldRep"
                    class="inline-flex items-center text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                    <svg class="h-4 w-4 mr-1 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="currentColor" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64s14.3 32 32 32h384
            c17.7 0 32-14.3 32-32s-14.3-32-32-32h-96l-7.2-14.3
            C307.4 6.8 296.3 0 284.2 0H163.8
            c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32
            L53.2 467c1.6 25.3 22.6 45 47.9 45h245.8
            c25.3 0 46.3-19.7 47.9-45L416 128z" />
                    </svg>
                    {{ __('Delete') }}
                </x-danger-button>

                <!-- RIGHT: Update + Cancel -->
                <div class="flex gap-4">
                    <x-primary-button x-on:click="loading = true; $wire.updateFieldRep().then(() => loading = false)"
                        x-bind:disabled="loading">
                        <span x-show="!loading">{{ __('Update') }}</span>
                        <span x-show="loading" class="animate-spin h-4 w-4 border-2 border-white rounded-full"></span>
                    </x-primary-button>

                    <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-field-rep-modal')">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                </div>
            </div>
        </form>

    </x-modal>
    {{-- phone formatter --}}
    <script>
        function formatPhone(event) {
            const input = event.target;
            const raw = input.value.replace(/\D/g, '');

            if (event.inputType && event.inputType.startsWith("delete")) {
                return;
            }

            let formatted = '';
            if (raw.length > 0) formatted = raw.slice(0, 3);
            if (raw.length >= 4) formatted += '-' + raw.slice(3, 6);
            if (raw.length >= 7) formatted += '-' + raw.slice(6, 10);

            input.value = formatted;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    </script>
</div>