<x-app-layout>
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8">
        @include('admin.dashboard.partials.org_change')
    </div>
    <div>
        @include('admin.dashboard.partials.top_headers')
    </div>
    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 mt-4 ">
        <div class="grid grid-cols-12 gap-3">
            <div class="col-span-12 lg:col-span-6 bg-white px-3 py-1 rounded-lg border flex flex-col justify-between">
                <h2 class="text-md font-semibold text-gray-500 mb-2 p-3">Purchase overview</h2>
                @include('admin.dashboard.partials.purchase_overview')
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-6 bg-white rounded-lg border">
                @include('admin.dashboard.partials.supplier_orders')
            </div>


        </div>
    </div>

    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 mt-4 ">
        <div class="grid grid-cols-10 gap-3">
            <div class="col-span-10 md:col-span-10 lg:col-span-10 bg-white px-3 py-1 rounded-lg border" style="position:relative">
                <h2 class="text-md font-semibold text-gray-500 mb-2 p-3 uppercase">Open Purchase orders Status</h2>
                @include('admin.dashboard.partials.recent_purchase_orders')
            </div>
        </div>
    </div>


    <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 mt-4 ">
        <div class="grid grid-cols-12 gap-3">
            <div class="col-span-10 md:col-span-6 lg:col-span-6 bg-white px-3 py-1 rounded-lg border">
                @include('admin.dashboard.partials.low_product_list')
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-6 bg-white px-3 py-1 rounded-lg border">
                @include('admin.dashboard.partials.top_pickups')
            </div>
        </div>
    </div>

</x-app-layout>