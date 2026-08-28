<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:ride-request-consume')]
#[Description('Command description')]
class RideRequestConsume extends Command
{
    public function handle()
    {
        Kafka::consumer(['ride-request-notification'])
            ->withConsumerGroupId('ride-request-notification-group')
            ->withHandler(function ($message) {

                // Get Kafka message body
                $body = $message->getBody();

                // Get nearby drivers
                $nearbyDrivers = $body['nearby_drivers'] ?? [];

                foreach ($nearbyDrivers as $driver) {

                    $driverId = $driver[0];
                    $distance = $driver[1];

                    $this->info("Driver ID: {$driverId}");
                    $this->info("Distance: {$distance}");
                }

            })
            ->build()
            ->consume();
    }
}