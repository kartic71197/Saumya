<?php

namespace App\Livewire\Notification;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Bell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    /**
     * Load latest notifications for dropdown
     * Called on page load and after actions
     */
    public function loadNotifications()
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $this->notifications = $user->notifications()
            ->latest()
            ->take(3)
            ->get();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $this->loadNotifications();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        Auth::user()?->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function mount()
    {
        $this->loadNotifications();
    }

    /**
     * When a notification is clicked from the bell dropdown:
     * - Mark the notification as read
     * - Redirect user to notifications page
     *- and refresh the notifications list and mark that notification as read
     */
    public function openNotification($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return;
        }

        // mark as read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // redirect to notifications page
        return redirect()->route('notifications.index')->with('refresh', true);
    }


    public function render()
    {
        return view('livewire.notification.bell');
    }
}
