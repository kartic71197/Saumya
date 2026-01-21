<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 px-4">

        <!-- Header -->
        <div class="flex justify-between items-center mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                    Mark all as read
                </button>
            </form>
        </div>

        <!-- Notifications List -->
        <div class="space-y-2">
            @forelse($notifications as $notification)
                <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-all
                            {{ $notification->read_at ? 'opacity-70' : 'shadow-sm' }}">
                    
                    <div class="px-4 py-3">
                        <!-- Organization Badge (if exists) -->
                        @if(!empty($notification->data['organization_name']))
                            <div class="mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-md
                                             bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $notification->data['organization_name'] }}
                                </span>
                            </div>
                        @endif

                        <!-- Title with Unread Indicator + Timestamp Row -->
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="flex items-start gap-2.5 flex-1">
                                @if(!$notification->read_at)
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></span>
                                @endif
                                <h3 class="text-base font-semibold text-gray-900 leading-tight">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </h3>
                            </div>
                            
                            <!-- Timestamp -->
                            <div class="flex items-center text-xs text-gray-400 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($notification->created_at)->format('m/d/Y') }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ \Carbon\Carbon::parse($notification->created_at)->format('g:i A') }}</span>
                            </div>
                        </div>

                        <!-- Message -->
                        @if(!empty($notification->data['message']))
                            <div class="text-sm text-gray-600 leading-snug mb-2.5 {{ !$notification->read_at ? 'ml-4.5' : '' }}">
                                {{ $notification->data['message'] }}
                            </div>
                        @endif

                        <!-- Creator/Actor Name -->
                        <!-- @php
                            $actorName = $notification->data['actor_name'] ?? 
                                        $notification->data['user_name'] ?? 
                                        $notification->data['created_by'] ?? 
                                        null;
                        @endphp -->

                        {{-- Invoice Actions + Actor --}}
                        @if(($notification->data['type'] ?? null) === 'invoice_uploaded' || $actorName)
                            <div class="mt-1 flex items-center justify-between {{ !$notification->read_at ? 'ml-4.5' : '' }}">

                                {{-- Actor --}}
                                <!-- @if($actorName)
                                    <div class="flex items-center"> -->
                                        <!-- <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 
                                                    flex items-center justify-center text-white text-xs font-semibold mr-2">
                                                        {{ strtoupper(substr($actorName, 0, 1)) }}
                                                    </div> -->
                                        <!-- <span class="text-xs font-medium text-red-600">
                                            {{ $actorName }}
                                        </span>
                                    </div>
                                @endif -->

                                {{-- ==========================================================
                                DOWNLOAD INVOICE BUTTON
                                ==========================================================

                                This button appears ONLY for invoice_uploaded notifications.

                                How it works:
                                1. Shows when notification type is 'invoice_uploaded'
                                2. Gets PO ID from notification data
                                3. Gets source type (manual/edi/stripe) from notification
                                4. Links to download route which calls InvoiceDownloadService

                                Route: notifications.invoices.download
                                Controller: NotificationController@downloadInvoice
                                Service: InvoiceDownloadService::downloadByPurchaseOrderId()
                                ========================================================== --}}
                                @if(($notification->data['type'] ?? null) === 'invoice_uploaded')
                                                <a href="{{ route('notifications.invoices.download', [
                                        'po' => $notification->data['purchase_order_id'],
                                        'source' => $notification->data['source'] ?? 'manual',
                                    ]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold
                                                                      text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md
                                                                      hover:bg-indigo-100 transition">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                                    </svg>
                                                    Download Invoice
                                                </a>
                                @endif

                                {{-- ==========================================================
                                Redirect to PO Page BUTTON
                                ==========================================================
                                This button appears ONLY for order_status_updated notifications.
                                How it works:
                                1. Shows when notification type is 'order_status_updated'
                                2. Determines route based on user role (admin or regular user)
                                3. Links to the appropriate purchase orders index page  
                                Route: admin.purchase.index or purchase.index
                                ========================================================== --}}
                                
                                @if(($notification->data['type'] ?? null) === 'order_status_updated')
                                    @php
                                        $poRoute = auth()->user()->role_id == 1
                                            ? route('admin.purchase.index')
                                            : route('purchase.index');
                                    @endphp
                                    <a href="{{ $poRoute }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold
                                                              text-green-700 bg-green-50 border border-green-200 rounded-md
                                                              hover:bg-green-100 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12H9m6 0l-3 3m3-3l-3-3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        View PO details
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">No notifications</h3>
                    <p class="text-sm text-gray-500">You're all caught up!</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>