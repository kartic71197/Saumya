<x-app-layout>
    <div class="max-w-10xl mx-auto px-4">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-5">
            <div class="text-xs">
                {{-- Livewire wrapper component contains the dropdown and table --}}
                <livewire:admin.reports.edi-report-component />
            </div>
        </div>
    </div>
</x-app-layout>
