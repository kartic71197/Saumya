<!-- resources/views/medrep/send_samples.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Send Samples') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:medical-rep.send-samples-component :locationId="$location->id ?? null" />
        </div>
    </div>
</x-app-layout>