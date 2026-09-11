<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::forRole(auth()->user()->role)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(SystemNotification $notification)
    {
        $notification->update(['is_read' => true]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        SystemNotification::forRole(auth()->user()->role)
            ->unread()
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function resolve(SystemNotification $notification)
    {
        $notification->update([
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Notification resolved.');
    }
}
