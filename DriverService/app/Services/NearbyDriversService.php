<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis; 

class NearbyDriversService
{
    private const AVAILABLE_DRIVERS_KEY = 'drivers:available:locations';

    public function nearbyDrivers(
        float $longitude,
        float $latitude,
        float $radiusKm = 5,
        int $limit = 20,
    ): array {
        $redis = Redis::connection()->client();

        return $redis->rawCommand(
            'GEOSEARCH',
            'laravel-database-drivers:available:locations',
            'FROMLONLAT',
            $longitude,
            $latitude,
            'BYRADIUS',
            $radiusKm,
            'km',
            'ASC',
            'COUNT',
            $limit,
            'WITHDIST',
        );
    }
}