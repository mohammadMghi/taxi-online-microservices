<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Junges\Kafka\Facades\Kafka;

class RejectRideController extends Controller
{
    public function reject(Request $request, $id)
    {
        $driver_id = $request->headers->get('X-User-ID');

        $ride = Redis::sMembers("driver:{$driver_id}:notifications");

        if (empty($ride)) {
            return response()->json([
                'message' => 'No ride request found for this driver.',
            ], 404);
        }
        
        $ride = Ride::findOrFail($id);
        if ($ride->status !== 'pending') {
            return response()->json([
                'message' => 'Ride cannot be rejected as it is not in pending status.',
            ], 400); 
        }

        $ride->status = 'rejected';
        $ride->save();

        Kafka::publish()
            ->onTopic('ride-rejected')
            ->withBodyKey('rideId', $ride->id)
            ->withBodyKey('driverId', $driver_id)
            ->send();

        return response()->json([
            'message' => 'Ride rejected successfully.',
            'data' => $ride,
        ]);
    }
}
