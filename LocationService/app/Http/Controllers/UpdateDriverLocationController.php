<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class UpdateDriverLocationController extends Controller
{
    private const AVAILABLE_DRIVERS_KEY = 'drivers:available:locations';

    public function updateLocation(Request $request)
    {
        $driverId = $request->input('userId');
        $lat = $request->input('lat');
        $long = $request->input('long');
 
        Redis::command('GEOADD' , [
            self::AVAILABLE_DRIVERS_KEY, $long, $lat, "driver:{$driverId}"
        ]);

        Redis::setex(
            "driver:{$driverId}:presence",
            15,
            '1'
        );
        
        Redis::hset(
            "driver:{$driverId}:state",
            'status',
            'available'
        );

        Redis::hset(
            "driver:{$driverId}:state",
            'last_seen',
            now()->timestamp
        );


        return response()->json([
            'message' => 'Driver location updated successfully',
            'userId' => $driverId,
            'lat' => $lat,
            'long' => $long,
        ]);
    }
}
