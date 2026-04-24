<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => empty($data) ? null : $data,
        ]);
    }

    /**
     * Send a notification to all users with the given Spatie role.
     */
    public function sendToRole(
        string $role,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        User::role($role)->get()->each(function (User $user) use ($type, $title, $message, $data) {
            $this->send($user, $type, $title, $message, $data);
        });
    }

    /**
     * Get recent notifications for a user (latest 30).
     */
    public function getForUser(User $user, int $limit = 30): Collection
    {
        return Notification::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)->unread()->count();
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Notification $notification): void
    {
        if ($notification->isUnread()) {
            $notification->update(['read_at' => now()]);
        }
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
