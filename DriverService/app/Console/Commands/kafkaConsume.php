<?php

namespace App\Console\Commands;

use App\Services\NearbyDriversService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command; 
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:consume')]
#[Description('Command description')]
class kafkaConsume extends Command
{
    protected $signature = 'kafka:consume';

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


                $this->info('Pickup Location: ' . $pickup_location);
                $this->info('Dropoff Location: ' . $dropoff_location);

                $nearbyDrivers = app(NearbyDriversService::class)->nearbyDrivers(
                    $pickup_long,
                    $pickup_lat,
                    5,
                    20
                );

                foreach($nearbyDrivers as [$driver, $distance]) {
                    $driverId = str_replace('driver:', '', $driver);    

                    if (!$driverId) {
                        continue;
                    }

                    $this->info("Driver ID: {$driverId}, Distance: {$distance}");

                    Kafka::publish()
                        ->onTopic('driver-notification')
                        ->withBodyKey('driverId', $driverId)
                        ->withBodyKey('pickup_location', $pickup_location)
                        ->withBodyKey('dropoff_location', $dropoff_location)
                        ->withBodyKey('dropoff_lat', $dropoff_lat)
                        ->withBodyKey('dropoff_lng', $dropoff_long)
                        ->withBodyKey('pickup_lat', $pickup_lat)
                        ->withBodyKey('pickup_lng', $pickup_long)
                        ->send();
                }   
            })
            ->build();

        $this->info('Consumer started. Waiting for messages...');

        //find near by drivers
        // $nearbyDrivers = app(NearbyDriversService::class)->nearbyDrivers(
        //     51.5074, // Example longitude
        //     -0.1278, // Example latitude
        //     5,       // Radius in kilometers
        //     20       // Limit of results
        // );

        //call notification service to send notification to nearby drivers

        $consumer->consume();
    }
}