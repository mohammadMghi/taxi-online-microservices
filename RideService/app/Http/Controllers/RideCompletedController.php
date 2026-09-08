<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;

class RideCompletedController extends Controller
{
    public function handle(Request $request, $id)
    {   
        $driver_id = $request->headers->get('X-User-ID');

        if (!\App\Models\Ride::where('id', $id)
                ->where('status' , 'accepted')
                ->where('driver_id', $driver_id)
                ->exists()
            ) {
            return response()->json([
                'message' => 'You are not authorized to complete this ride.',
            ], 403);
        }

        $updated = \App\Models\Ride::where('id', $id)
            ->where('status', 'accepted')
            ->where('driver_id', $driver_id)
            ->update([
                'status' => 'completed',
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Ride not found or already completed.',
            ], 404);
        }

        Kafka::publish()
            ->onTopic('ride-completed')
            ->withBodyKey('rideId', $id)
            ->withBodyKey('driverId', $driver_id)
            ->withBodyKey('status', 'completed')
            ->withBodyKey('cost' , \App\Models\Ride::where('id', $id)->value('cost'))
            ->send();

        return response()->json([
            'message' => 'Ride completed successfully.',
        ]); 
    }
}