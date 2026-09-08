<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Junges\Kafka\Facades\Kafka;

class AccpetRideController extends Controller
{
    public function accept(Request $request, $id)
    {  
        $foundedRide = null;
        
        $driver_id = $request->headers->get('X-User-ID');

        $ride = Redis::sMembers("driver:{$driver_id}:notifications");
  
        foreach ($ride as $rideData) {
            $rideDetails = json_decode($rideData, true);
            if (isset($rideDetails['rideId']) && $rideDetails['rideId'] == $id) {
                $foundedRide = Ride::find($id);
                break;
            }
        } 

        if (!isset($foundedRide) || !$foundedRide) {
            return response()->json([
                'message' => 'No ride request found for this driver.',
            ], 404);
        }

        $updated = Ride::query()
            ->where('id', $id)
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->update([
                'status' => 'accepted',
                'driver_id' => $driver_id,
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Ride has already been accepted.',
            ], 409);
        }
    
        Kafka::publish()
            ->onTopic('ride-accepted')
            ->withBodyKey('rideId', $foundedRide->id)
            ->withBodyKey('driverId', $driver_id)
            ->send();

        return response()->json([
            'message' => 'Ride accepted successfully.',
            'data' => $foundedRide,
        ]);
    }
}
