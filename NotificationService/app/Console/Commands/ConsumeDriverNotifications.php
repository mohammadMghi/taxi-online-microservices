<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:consume-driver-notifications')]
#[Description('Command description')]
class ConsumeDriverNotifications extends Command
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
                        'rideId' => $body['rideId'],
                        'driver_id' => $body['driverId'],
                        'user_id' => $body['userId'],
                    ])
                );
   
                $notification = new Notification();

                $notification->pickup_location = $body['pickup_location'];
                $notification->dropoff_location = $body['dropoff_location'];
                $notification->pickup_lat = $body['pickup_lat'];
                $notification->pickup_lng = $body['pickup_lng'];
                $notification->dropoff_lat = $body['dropoff_lat'];
                $notification->dropoff_lng = $body['dropoff_lng'];
                $notification->user_id = (int) $body['userId'];
                $notification->driver_id = (int) $body['driverId'];
                $notification->ride_request_id = (int) $body['rideId'];
                $notification->save();

                $this->info("Notification sent to driver: " . json_encode($body['driverId']));
            })
            ->build()
            ->consume();
    }
}