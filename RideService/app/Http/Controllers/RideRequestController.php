<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Junges\Kafka\Facades\Kafka;

class RideRequestController extends Controller
{
    public function handle(Request $request)
    { 
        $validatedData = $request->validate([ 
            'pickup_location' => 'required|string',
            'dropoff_location' => 'required|string',
            'dropoff_lat' => 'required|integer',
            'dropoff_lng' => 'required|integer',
            'pickup_lat' => 'required|integer',
            'pickup_lng' => 'required|integer',
        ]);
 
        $user_id = $request->headers->get('X-User-ID');

        $ride = Ride::create([
            'user_id' => $user_id,
            'status' => 'pending',
            'pickup_location' => $request->input('pickup_location'),
            'dropoff_location' => $request->input('dropoff_location'),
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
        ]);
 
        Kafka::publish()
            ->onTopic('ride-requested')
            ->withBodyKey('rideId', $ride->id)
            ->withBodyKey('userId', $user_id)
            ->withBodyKey('pickup_location', $request->input('pickup_location'))
            ->withBodyKey('dropoff_location', $request->input('dropoff_location'))
            ->withBodyKey('dropoff_lat', $request->input('dropoff_lat'))
            ->withBodyKey('dropoff_lng', $request->input('dropoff_lng'))
            ->withBodyKey('pickup_lat', $request->input('pickup_lat'))
            ->withBodyKey('pickup_lng', $request->input('pickup_lng'))
            ->send();
 
        return response()->json([
            'message' => 'Ride request received successfully.',
            'data' => $validatedData,
        ]);
    }

    public function accept(Request $request, $id)
    {  
        $driver_id = $request->headers->get('X-User-ID');

        $ride = Redis::sMembers("driver:{$driver_id}:notifications");

        if (empty($ride)) {
            return response()->json([
                'message' => 'No ride request found for this driver.',
            ], 404);
        }

        $ride = Ride::findOrFail($id);
        $ride->status = 'accepted';
        $ride->driver_id = $driver_id;
        $ride->save();

        Kafka::publish()
            ->onTopic('ride-accepted')
            ->withBodyKey('rideId', $ride->id)
            ->withBodyKey('driverId', $driver_id)
            ->send();

        return response()->json([
            'message' => 'Ride accepted successfully.',
            'data' => $ride,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $driver_id = $request->headers->get('X-User-ID');
        
        $ride = Ride::findOrFail($id);
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
