<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RemoveLocationController extends Controller
{
    private const AVAILABLE_DRIVERS_KEY = 'drivers:available:locations';
    public function removeDriver(Request $request): void
    {
        $driverId = $request->input('userId');

        Redis::command('ZREM', [
            self::AVAILABLE_DRIVERS_KEY,
            "driver:{$driverId}",
        ]);

        Redis::del([
            "driver:{$driverId}:presence",
        ]);

        Redis::hset(
            "driver:{$driverId}:state",
            'status',
            'offline'
        );
    }
}
