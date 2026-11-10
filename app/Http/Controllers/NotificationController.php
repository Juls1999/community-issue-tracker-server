<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get authenticated user's notifications.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'unread_count' => $request->user()
                ->unreadNotifications()
                ->count(),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $notification)
    {
        $userNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $userNotification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroyRead(Request $request)
    {
        $request->user()
            ->readNotifications()
            ->delete();

        return response()->noContent();
    }

    public function destroy(Request $request, string $notification)
    {
        $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail()
            ->delete();

        return response()->noContent();
    }
}
