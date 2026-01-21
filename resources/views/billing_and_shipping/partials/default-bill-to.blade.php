<div>
    @php
        $isReadonly = auth()->user()->role_id != 1;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Default Billing Location --}}
        <div>
            <label class="block text-sm font-medium mb-1">Default Billing Location</label>
            <select id="defaultBilling" class="w-full border rounded p-2" {{ $isReadonly ? 'disabled' : '' }}
                onchange="updateDefault('billing', this.value)">
                <option value="">Select location</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ $location->is_default ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Default Shipping Location --}}
        <div>
            <label class="block text-sm font-medium mb-1">Default Shipping Location</label>
            <select id="defaultShipping" class="w-full border rounded p-2" {{ $isReadonly ? 'disabled' : '' }}
                onchange="updateDefault('shipping', this.value)">
                <option value="">Select location</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ $location->is_default_shipping ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>
    <script>
        function updateDefault(type, locationId) {
            if (!locationId) return;

            fetch(`/locations/update-default/{{ $organization_id }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: type,
                    location_id: locationId
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message);
                    }
                })
                .catch(() => alert('Something went wrong'));
        }
    </script>


</div>