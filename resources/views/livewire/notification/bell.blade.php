<div x-data="{ open: false }" class="relative">

    {{-- Bell Icon --}}
    <button @click.stop="open = !open" wire:click="loadNotifications" class="relative inline-flex items-center px-3 py-2
           text-gray-500 dark:text-gray-400
           hover:text-gray-700 dark:hover:text-gray-300
           focus:outline-none transition ease-in-out duration-150">
        {{-- Bell SVG --}}
        <span class="text-lg">
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 512 512">
                <path d="M439.652,347.915v-97.48c0-85.797-59.14-158.031-138.794-178.101c3.34-6.707,5.229-14.258,5.229-22.246 
            C306.087,22.469,283.618,0,256,0c-27.618,0-50.087,22.469-50.087,50.087c0,7.988,1.889,15.539,5.229,22.246 
            c-79.654,20.07-138.794,92.305-138.794,178.101v97.48c-19.433,6.892-33.391,25.45-33.391,47.215 
            c0,27.618,22.469,50.087,50.087,50.087h85.158C181.957,483.275,215.686,512,256,512s74.042-28.725,81.799-66.783h85.158 
            c27.618,0,50.087-22.469,50.087-50.087C473.043,373.365,459.085,354.807,439.652,347.915z 
            M256,33.391c9.206,0,16.696,7.49,16.696,16.696S265.206,66.783,256,66.783c-9.206,0-16.696-7.49-16.696-16.696
            S246.794,33.391,256,33.391z 
            M256,478.609c-21.766,0-40.323-14.07-47.215-33.503h94.431C296.323,464.539,277.766,478.609,256,478.609z 
            M422.957,411.826H89.044c-9.206,0-16.696-7.49-16.696-16.696s7.49-16.696,16.696-16.696h33.392c9.22,0,16.696-7.475,16.696-16.696
            s-7.475-16.696-16.696-16.696h-16.697v-94.609c0-82.854,67.407-150.261,150.261-150.261s150.261,67.407,150.261,150.261v94.609
            h-16.71c-9.22,0-16.696,7.475-16.696,16.696s7.475,16.696,16.696,16.696h33.406c9.206,0,16.696,7.49,16.696,16.696
            S432.162,411.826,422.957,411.826z"></path>
                <path d="M256,133.565c-64.442,0-116.87,52.428-116.87,116.87c0,9.22,7.475,16.696,16.696,16.696s16.696-7.475,16.696-16.696
            c0-46.03,37.448-83.478,83.478-83.478c9.22,0,16.696-7.475,16.696-16.696S265.22,133.565,256,133.565z"></path>
            </svg>
        </span>

        {{-- Unread Badge --}}
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white text-xs rounded-full px-2 py-1">
                {{ $unreadCount }}
            </span>

        @endif
    </button>

    {{-- Dropdown 
    x-cloak prevents notification flash on page load - now opens only when triggered.--}}
    <div x-show="open" x-cloak  @click.outside="open = false"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 shadow-lg rounded-lg z-50">
        <div class="flex justify-between items-center px-4 py-2 border-b">
            <h3 class="font-semibold">Notifications</h3>

            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-sm text-blue-600 hover:underline">
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $notification)
                <div wire:key="{{ $notification->id }}" class="px-4 py-3 border-b cursor-pointer
                                    {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-gray-700' : '' }}" {{--
                    Clicking a notification: - Calls Livewire method to mark it as read - Redirects user to notifications
                    page --}} wire:click="openNotification('{{ $notification->id }}')">
                    {{-- ADD THIS: Organization Badge --}}
                    @if(!empty($notification->data['organization_name']))
                        <div class="mb-1">
                            <span
                                class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full
                                                                 bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ $notification->data['organization_name'] }}
                            </span>
                        </div>
                    @endif
                    <p class="font-medium">
                        {{ $notification->data['title'] ?? 'Notification' }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $notification->data['message'] ?? '' }}
                    </p>
                </div>
            @empty
                <p class="px-4 py-4 text-sm text-gray-500">
                    No notifications
                </p>
            @endforelse
        </div>

        {{-- View All --}}
        <div class="px-4 py-2 text-center border-t">
            <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">
                View all notifications
            </a>
        </div>
    </div>
</div>