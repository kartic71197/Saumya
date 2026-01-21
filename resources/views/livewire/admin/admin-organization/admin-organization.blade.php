<div>
    <div>
        @if($show_org_details)
            @include('livewire.admin.admin-organization.org-details')
        @else
            {{-- @include('livewire.admin.admin-organization.org-cards') --}}
            <div class="py-12">
                <div class="max-w-screen-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="w-full">
                            <header
                                class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-3 border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                                <div>
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ $isRepOrg ? __('Manage MedRep Practices') : __('Manage Practices') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $isRepOrg
            ? __('Review and manage MedRep practices as required.')
            : __('Review and manage practices as required.') 
                                                   }}
                                    </p>
                                </div>
                                {{-- RIGHT SIDE BUTTON --}}
                                <div>
                                    <x-primary-button 
                                    x-data="{ loading: false }"
                                        x-on:click="loading = true; $wire.openCreatePracticeModal(); setTimeout(() => loading = false, 1000)"
                                        x-bind:disabled="loading">
                                        + Create Practice
                                    </x-primary-button>
                                </div>
                            </header>
                        </div>
                        <div class="text-xs">
                            <livewire:tables.admin.organizations-list :is-rep-org="$isRepOrg" />
                        </div>
                        @include('livewire.admin.admin-organization.create-practice-modal')
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit Organization Modal -->
        <x-modal name="edit-organization-modal" width="w-100" height="h-auto" maxWidth="4xl">
            <header class="p-3">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Edit Practice') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Edit the Practice details, and those fields marked with * are compulsory.') }}
                </p>
            </header>
            <form wire:submit.prevent="updateOrganization">
                <div class="space-y-12 p-3">
                    <div class="border-b border-gray-900/10 pb-12 px-6">
                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <!-- Name -->
                            <div class="sm:col-span-3">
                                <div class="flex items-center">
                                    <x-input-label for="name" value="Practice's Name" />
                                    <span class="text-red-500 ml-0.5">*</span>
                                </div>
                                <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full"
                                    required />
                                @error('name')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="sm:col-span-3">
                                <div class="flex items-center">
                                    <x-input-label for="email" value="Email" />
                                    <span class="text-red-500 ml-0.5">*</span>
                                </div>
                                <x-text-input id="email" wire:model="email" type="email" class="mt-1 block w-full"
                                    required />
                                @error('email')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Plan -->
                            <div class="sm:col-span-3">
                                <div class="flex items-center">
                                    <x-input-label for="plan_id" value="Plan" />
                                    <span class="text-red-500 ml-0.5">*</span>
                                </div>
                                <select wire:model="plan_id" id="plan_id"
                                    class="block w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1"
                                    required>
                                    <option value="">Select Plan</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="sm:col-span-3">
                                <x-input-label for="phone" :value="__('Phone Number')" />
                                <div class="flex mt-1">
                                    <span
                                        class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300">+1</span>
                                    <x-text-input id="phone" wire:model.lazy="phone" type="tel" maxlength="10"
                                        placeholder="123-456-7890" @blur="formatPhone($event)"
                                        class="block w-full rounded-none rounded-r-md" />
                                </div>
                                @error('phone')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address (Full Width) -->
                            <div class="sm:col-span-6">
                                <label for="address"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address
                                    <span class="text-red-500">*</span>
                                </label>
                                <input id="address" wire:model="address" type="text"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1" />
                            </div>

                            <!-- City -->
                            <div class="sm:col-span-3">
                                <label for="city"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    City <span class="text-red-500">*</span>
                                </label>
                                <input id="city" wire:model="city" type="text"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1"
                                    required />
                            </div>

                            <!-- State/Province -->
                            <div class="sm:col-span-3">
                                <label for="state"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    State/Province <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="state"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1"
                                    required>
                                    <option value="">Select a State</option>
                                    @foreach ($states as $st)
                                        <option value="{{ $st }}" @selected($st == $state)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Country -->
                            <div class="sm:col-span-3">
                                <label for="country"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Country <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="selectedCountry"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1"
                                    required>
                                    <option value="">Select a Country</option>
                                    @foreach (array_keys($countries) as $country)
                                        <option value="{{ $country }}">{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Zip/Postal Code -->
                            <div class="sm:col-span-3">
                                <label for="pin"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Zip/Postal Code <span class="text-red-500">*</span>
                                </label>
                                <input id="pin" wire:model="pin" type="text"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1"
                                    required />
                            </div>

                            <!-- Logo Upload -->
                            <div class="sm:col-span-6">
                                <x-input-label for="logo" :value="__('Practice\'s Logo')" />

                                <div class="mt-1 flex items-center gap-4">
                                    <!-- File Input -->
                                    <div class="flex-1">
                                        <input type="file" id="logo" wire:model="logo"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-primary-md focus:ring-primary-md focus:ring-1 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-lt file:text-primary-dk hover:file:bg-primary-md hover:file:text-white file:transition-colors">
                                        @error('logo')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Logo Preview -->
                                    @if ($logo || !empty($organization?->image))
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-16 h-16 rounded-lg border-2 border-gray-200 dark:border-gray-600 overflow-hidden bg-gray-50 dark:bg-gray-700">
                                                @if ($logo)
                                                    <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover"
                                                        alt="Logo preview">
                                                @elseif (!empty($organization?->image))
                                                    <img src="{{ asset('storage/' . $organization->image) }}"
                                                        class="w-full h-full object-cover" alt="Current logo">
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
                                                {{ $logo ? 'New' : 'Current' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Active checkbox -->
                            <div class="sm:col-span-6">
                                <div class="flex gap-3 items-start">
                                    <div class="flex h-6 shrink-0 items-center mt-1">
                                        <input id="is_active" wire:model="is_active" type="checkbox"
                                            class="rounded border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 text-primary-md focus:ring-primary-md focus:ring-2">
                                    </div>
                                    <div class="text-sm/6">
                                        <x-input-label for="is_active" :value="__('Is Active')" />
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('This practice will be active and available for users.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-x-6 px-6">
                        <!-- Delete Button -->
                        <x-danger-button type="button" wire:click="deleteOrganization"
                            wire:confirm="Are you sure you want to delete this practice?"
                            class="inline-flex items-center text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                <path fill="currentColor"
                                    d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z" />
                            </svg>
                            {{ __('Delete') }}
                        </x-danger-button>

                        <!-- Cancel Button -->
                        <x-secondary-button type="button"
                            x-on:click="$dispatch('close-modal', 'edit-organization-modal')"
                            class="text-sm font-semibold">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <!-- Submit Button with Loader -->
                        <x-primary-button type="submit"
                            class="min-w-24 flex justify-center items-center text-sm font-semibold"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Update</span>
                            <span wire:loading class="flex justify-center items-center">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C6.477 0 0 6.477 0 12h4z"></path>
                                </svg>
                            </span>
                        </x-primary-button>

                    </div>
                </div>
            </form>
        </x-modal>
    </div>
    <!-- JavaScript for Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchSettings = document.getElementById('searchSettings');
            if (searchSettings) {
                searchSettings.addEventListener('input', function () {
                    let filter = this.value.toLowerCase();
                    let settingCards = document.querySelectorAll('.setting-card');

                    settingCards.forEach(card => {
                        let title = card.getAttribute('data-title');
                        card.style.display = title.includes(filter) ? 'block' : 'none';
                    });
                });
            }
        });
    </script>

    <script>
        function impersonateUser(userId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/impersonate/' + userId;

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrfToken.content;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
        function formatPhone(event) {
            const input = event.target;
            const raw = input.value.replace(/\D/g, ''); // Strip all non-digits

            // Avoid formatting when deleting (allow user control)
            if (event.inputType && event.inputType.startsWith("delete")) {
                return;
            }

            let formatted = '';
            if (raw.length > 0) {
                formatted = raw.slice(0, 3);
            }
            if (raw.length >= 4) {
                formatted += '-' + raw.slice(3, 6);
            }
            if (raw.length >= 7) {
                formatted += '-' + raw.slice(6, 10);
            }

            input.value = formatted;

            // Livewire sync
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    </script>
</div>