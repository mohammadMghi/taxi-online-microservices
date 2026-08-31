<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;

class RideRequestController extends Controller
{
    public function handle(Request $request)
    { 
        $validatedData = $request->validate([ 
            'pickup_location' => 'required|string',
            'dropoff_location' => 'required|string',
        ]);
 
        $headers = $request->headers->get('X-User-ID');
 
        Kafka::publish()
            ->onTopic('ride-requested')
            ->withBodyKey('userId', $headers)
            ->withBodyKey('pickup_location', $request->input('pickup_location'))
            ->withBodyKey('dropoff_location', $request->input('dropoff_location'))
            ->send();
 
        return response()->json([
            'message' => 'Ride request received successfully.',
            'data' => $validatedData,
        ]);
    }
}
