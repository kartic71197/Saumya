<div class="grid grid-cols-2 p-3 gap-2">
            <div class="bg-gray-100 dark:bg-gray-400 p-4 rounded shadow-sm">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1 hidden">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Billing Information</h3>
                        <div class="text-gray-800 dark:text-gray-800">
                            <p class="text-sm py-1">
                                <span class="text-primary-dk">Location :</span>
                                {{ $purchaseOrder->billingLocation->name ?? 'N/A' }}
                            </p>
                            <p class="text-sm py-1 ">
                                <span class="text-primary-dk">Contact:</span>
                                {{ $purchaseOrder->billingLocation->email ?? 'N/A' }}
                            </p>
                            <p class="text-sm py-1">
                                <span class="text-primary-dk">Bill to number:</span>
                                #{{ $purchaseOrder->bill_to ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Shipping Information</h3>
                        <div class="text-gray-800 dark:text-gray-800">
                            <p class="text-sm py-1">
                                <span class="text-primary-dk">Location :</span>
                                {{ $purchaseOrder->shippingLocation->name ?? 'N/A' }}
                            </p>
                            <p class="text-sm py-1 ">
                                <span class="text-primary-dk">Contact:</span>
                                {{ $purchaseOrder->shippingLocation->email ?? 'N/A' }}
                            </p>
                            <p class="text-sm py-1">
                                <span class="text-primary-dk">Ship to number:</span>
                                #{{ $purchaseOrder->bill_to ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="block w-full min-w-0 rounded-lg bg-primary-md p-6 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 max-h-[175px]">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-50 ">
                    {{ $purchaseOrder->purchaseSupplier->supplier_name ?? 'Supplier' }}
                </h5>
                <p class="font-normal text-gray-100 ">
                    {{ $purchaseOrder->purchaseSupplier->supplier_email ?? 'Email' }}
                </p>
                <p class="font-normal text-gray-100">
                    {{ $purchaseOrder->purchaseSupplier->supplier_phone ?? 'Phone' }}
                </p>
                <p class="font-normal text-gray-100 mt-2">
                    @php
                        $address = [];
                        $supplier = $purchaseOrder?->purchaseSupplier;
                        if ($supplier?->supplier_address) {
                            $address[] = e($supplier->supplier_address);
                        }
                        // Combine city and state if both exist
                        $cityState = collect([$supplier?->supplier_city, $supplier?->supplier_state])
                            ->filter()
                            ->join(', ');
                        if ($cityState) {
                            $address[] = e($cityState);
                        }
                        if ($supplier?->supplier_country) {
                            $address[] = e($supplier->supplier_country);
                        }
                        if ($supplier?->supplier_zip) {
                            $address[] = ' (' . e($supplier->supplier_zip) . ')';
                        }
                        $formattedAddress = implode(', ', $address);
                    @endphp
                    {{ !empty($address) ? $formattedAddress : 'No address available' }}
                </p>
            </div>
        </div>