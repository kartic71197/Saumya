<x-modal name="create-practice-modal" maxWidth="4xl">
    <header class="p-3">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Create Practice') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Create a new practice. Default roles will be created automatically.') }}
        </p>
    </header>

    <form wire:submit.prevent="createPractice">
        <div class="space-y-8 p-4">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-6">

                {{-- Practice Name --}}
                <div class="sm:col-span-3">
                    <x-input-label value="*Practice Name" />
                    <x-text-input wire:model="name" class="w-full" />
                    @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Email --}}
                <div class="sm:col-span-3">
                    <x-input-label value="*Email" />
                    <x-text-input wire:model="email" type="email" class="w-full" />
                    @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Phone --}}
                <div class="sm:col-span-2">
                    <x-input-label value="Phone" />
                    <x-text-input wire:model.lazy="phone" class="w-full" />
                </div>

                {{-- Street Address --}}
                <div class="sm:col-span-4">
                    <x-input-label value="*Street Address" />
                    <x-text-input wire:model="address" class="w-full" />
                    @error('address') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Country --}}
                <div class="sm:col-span-2">
                    <x-input-label value="*Country" />
                    <select wire:model.live="selectedCountry" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select Country</option>
                        @foreach (array_keys($countries) as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                    @error('selectedCountry') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- State --}}
                <div class="sm:col-span-2">
                    <x-input-label value="*State / Province" />
                    <select wire:model="state" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select State</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}">{{ $state }}</option>
                        @endforeach
                    </select>
                    @error('state') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- City --}}
                <div class="sm:col-span-2">
                    <x-input-label value="*City" />
                    <x-text-input wire:model="city" class="w-full" />
                    @error('city') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Zip --}}
                <div class="sm:col-span-2">
                    <x-input-label value="*Zip / Postal Code" />
                    <x-text-input wire:model="pin" class="w-full" />
                    @error('pin') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Logo --}}
                <div class="sm:col-span-6">
                    <x-input-label value="Logo" />
                    <input type="file" wire:model="logo">
                </div>

                <!-- Med Rep checkbox -->
                <!-- <div class="sm:col-span-3">
                    <div class="flex gap-3">
                        <div class="flex h-6 shrink-0 items-center">
                            <input id="isRepOrg" wire:model="isRepOrg" type="checkbox"
                                class="appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        </div>
                        <div class="text-sm/6">
                            <x-input-label for="isRepOrg" :value="__('Is Medical Rep Practice')" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('This Is_Medical_Rep feature is used to identify medical representative practices') }}
                            </p>
                        </div>
                    </div>
                </div> -->


            </div>
        </div>

        <div class="flex justify-end gap-3 p-4">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'create-practice-modal')">
                Cancel
            </x-secondary-button>

            <x-primary-button>
                Create
            </x-primary-button>
        </div>
    </form>
</x-modal>