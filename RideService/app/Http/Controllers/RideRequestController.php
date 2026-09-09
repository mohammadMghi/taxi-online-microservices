<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request; 
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
            'idempotency_id' => 'required|integer'
        ]);
 
        $user_id = $request->headers->get('X-User-ID');

        if (Ride::where('idempotency_id' , $request->idempotency_id)->exists()) {
            return response()->json([
                'message' => 'Ride already sent.',
                'data' => $validatedData,
            ]);
        }

        $ride = Ride::create([
            'user_id' => $user_id,
            'status' => 'pending',
            'cost' => 10000,
            'pickup_location' => $request->input('pickup_location'),
            'dropoff_location' => $request->input('dropoff_location'),
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
            'idempotency_id' => $request->input('idempotency_id'),
        ]);
 
        Kafka::publish()
            ->onTopic('ride-requested')
            ->withBodyKey('rideId', $ride->id)
            ->withBodyKey('idempotency_id', $request->idempotency_id)
            ->withBodyKey('userId', $user_id)
            ->withBodyKey('cost', $ride->cost)
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
}
