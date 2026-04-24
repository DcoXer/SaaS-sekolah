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

        return response()->json(['ok' => true]);
    }
}
