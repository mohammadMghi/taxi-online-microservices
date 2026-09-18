<?php

namespace App\Console\Commands;

use App\Services\NearbyDriversService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command; 
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:consume-nearby-drivers')]
#[Description('Command description')]
class ConsumeNearbyDrivers extends Command
{ 

    public function handle(): void
    {
        $this->info('Starting Kafka consumer...');

        $consumer = Kafka::consumer(['nearby-drivers-found'])
            ->withConsumerGroupId('driver-service-group')
            ->withHandler(function (ConsumerMessage $message) {
                $this->info('MESSAGE RECEIVED');
 
                $pickup_location = json_encode($message->getBody()['pickup_location']);
                $dropoff_location = json_encode($message->getBody()['dropoff_location']);
                $dropoff_lat = json_encode($message->getBody()['dropoff_lat']);
                $dropoff_long = json_encode($message->getBody()['dropoff_lng']);
                $pickup_lat = json_encode($message->getBody()['pickup_lat']);
                $pickup_long = json_encode($message->getBody()['pickup_lng']);
                $user_id = json_encode($message->getBody()['user_id']);
                $ride_id = json_encode($message->getBody()['ride_id']);
                $request_id = json_encode($message->getBody()['request_id']);


                $this->info('Pickup Location: ' . $pickup_location);
                $this->info('Dropoff Location: ' . $dropoff_location);

                $nearbyDrivers = app(NearbyDriversService::class)->nearbyDrivers(
                    $pickup_long,
                    $pickup_lat,
                    5,
                    20
                );

                $this->info("NearbyDrivers: {$nearbyDrivers}");

                foreach($nearbyDrivers as [$driver, $distance]) {

                    $this->info("foreach");

                    $this->info("foreach driver: {$driver} distance: {$distance}");

                    $driverId = str_replace('driver:', '', $driver);    
                    
                    if (!$driverId) {
                        continue;
                    }

                    $this->info("Driver ID: {$driverId}, Distance: {$distance}");

                    Kafka::publish()
                        ->onTopic('driver-notification')
                        ->withBodyKey('driverId', $driverId)
                        ->withBodyKey('userId', $user_id)
                        ->withBodyKey('pickup_location', $pickup_location)
                        ->withBodyKey('dropoff_location', $dropoff_location)
                        ->withBodyKey('dropoff_lat', $dropoff_lat)
                        ->withBodyKey('dropoff_lng', $dropoff_long)
                        ->withBodyKey('pickup_lat', $pickup_lat)
                        ->withBodyKey('pickup_lng', $pickup_long)
                        ->withBodyKey('rideId', $ride_id)
                        ->withBodyKey('request_id', $request_id)
                        ->send();
                }   
            })
            ->build();

        $this->info('Consumer started. Waiting for messages...');
 
        $consumer->consume();
    }
}