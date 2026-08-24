<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;

class LocationController extends Controller
{
    public function handle(Request $request)
    {
        $location = $request->input('lat'); 

        $headers = $request->headers->get('X-User-ID');

        //verified location

        //send event location is verified to the driver serivce (kafka)
        Kafka::publish()
            ->onTopic('x')
            ->withBodyKey('userId', $headers)
            ->withBodyKey('lat', $request->input('lat'))
            ->withBodyKey('long', $request->input('long'))
            ->send();

        return response()->json(['message' => 'Location received successfully', 'location' => $location , 'headers' => $headers]);
    }
}
