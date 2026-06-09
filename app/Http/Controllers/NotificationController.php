<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $service) {}

    public function markRead(Notification $notification)
    {
        abort_if($notification->user_id !== request()->user()->id, 403);

        $this->service->markRead($notification);

        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        $this->service->markAllRead(request()->user());

        return redirect()->back();
    }

    public function poll()
    {
        $user = request()->user();

        $notifications = $this->service->getForUser($user, 30)->map(fn ($n) => [
            'id'         => $n->id,
            'type'       => $n->type,
            'title'      => $n->title,
            'message'    => $n->message,
            'data'       => $n->data,
            'read_at'    => $n->read_at?->toISOString(),
            'created_at' => $n->created_at->diffForHumans(),
        ]);

        return response()->json([
            'notifications' => $notifications,
            'unreadCount'   => $this->service->getUnreadCount($user),
        ]);
    }
}
