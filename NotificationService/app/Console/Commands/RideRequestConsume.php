<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:comsume-notification')]
#[Description('Command description')]
class RideRequestConsume extends Command
{
    public function handle()
    {
        Kafka::consumer(['driver-notification'])
            ->withConsumerGroupId('notification-service-group')
            ->withHandler(function ($message) { 
                // Get Kafka message body
                $body = $message->getBody();

                Redis::sAdd(
                    "driver:{$body['driverId']}:notifications",
                    json_encode([
                        'pickup_location' => $body['pickup_location'],
                        'dropoff_location' => $body['dropoff_location'],
                        'dropoff_lat' => $body['dropoff_lat'],
                        'dropoff_lng' => $body['dropoff_lng'],
                        'pickup_lat' => $body['pickup_lat'],
                        'pickup_lng' => $body['pickup_lng'],
                    ])
                );

                $this->info("Notification sent to driver: " . json_encode($body['driverId']));
            })
            ->build()
            ->consume();
    }
}