<div class="p-6 max-w-xl">
    <h2 class="text-xl font-semibold mb-4">Create Appointment</h2>

    <!-- Service -->
    <label class="block mb-2">Service</label>
    <select wire:model="category_id"
        class="w-full border rounded px-3 py-2 mb-4">
        <option value="">Select service</option>
        @foreach($leafCategories as $cat)
            <option value="{{ $cat->id }}">
                {{ $cat->name }} ({{ $cat->duration }} min)
            </option>
        @endforeach
    </select>

    <!-- Staff -->
    <label class="block mb-2">Staff</label>
    <select wire:model="staff_id"
        class="w-full border rounded px-3 py-2 mb-4">
        <option value="">Select staff</option>
        @foreach($staff as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>

    <!-- Customer -->
    <label class="block mb-2">Customer</label>
    <select wire:model="customer_id"
        class="w-full border rounded px-3 py-2 mb-4">
        <option value="">Select customer</option>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
        @endforeach
    </select>

    <!-- Date -->
    <label class="block mb-2">Date</label>
    <input type="date" wire:model="date"
        class="w-full border rounded px-3 py-2 mb-4">

    <!-- Start Time -->
    <label class="block mb-2">Start Time</label>
    <select wire:model="start_time"
        class="w-full border rounded px-3 py-2 mb-4">
        <option value="">Select time</option>
        @for ($h = 9; $h <= 20; $h++)
            @for ($m = 0; $m < 60; $m += 5)
                <option value="{{ sprintf('%02d:%02d', $h, $m) }}">
                    {{ sprintf('%02d:%02d', $h, $m) }}
                </option>
            @endfor
        @endfor
    </select>

    <button wire:click="save"
        class="px-4 py-2 bg-blue-600 text-white rounded">
        Book Appointment
    </button>
</div>
