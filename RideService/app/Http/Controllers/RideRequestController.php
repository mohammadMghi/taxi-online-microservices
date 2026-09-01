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
        ]);
 
        $user_id = $request->headers->get('X-User-ID');

        Ride::create([
            'user_id' => $user_id,
            'pickup_location' => $request->input('pickup_location'),
            'dropoff_location' => $request->input('dropoff_location'),
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
        ]);
 
        Kafka::publish()
            ->onTopic('ride-requested')
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
}
