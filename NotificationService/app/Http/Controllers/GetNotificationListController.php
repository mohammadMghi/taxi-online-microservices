<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetNotificationListController extends Controller
{
    public function __invoke(Request $request)
    {
        $driver_id = $request->headers->get('X-User-ID');

        if (!$driver_id) {
            return response()->json(['error' => 'Driver ID is required'], 400);
        }

        $notifications = \App\Models\Notification::where('driver_id', $driver_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }
}
