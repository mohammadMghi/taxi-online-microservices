<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:validation-location')]
#[Description('Command description')]
class ValidationLocationCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Kafka::consumer(['ride-requested'])
            ->withConsumerGroupId('location-service-group')
            ->withHandler(function ($message) {
                
                // Get Kafka message body
                $body = $message->getBody();

                // Get pickup and dropoff locations
                $pickupLocation = $body['pickup_location'] ?? null;
                $dropoffLocation = $body['dropoff_location'] ?? null;
                $request_id = $body['request_id'] ?? null; 

                // Validate locations
                if (!$pickupLocation || !$dropoffLocation) {
                    $this->error('Invalid ride request: Missing pickup or dropoff location.');
                    return;
                }

                Kafka::publish()
                    ->onTopic('nearby-drivers-found')
                    ->withBodyKey('user_id', $body['userId'])
                    ->withBodyKey('pickup_location', $pickupLocation)
                    ->withBodyKey('dropoff_location', $dropoffLocation)
                    ->withBodyKey('dropoff_lng', $body['dropoff_lng'] ?? null)
                    ->withBodyKey('dropoff_lat', $body['dropoff_lat'] ?? null)
                    ->withBodyKey('pickup_lat', $body['pickup_lat'] ?? null)
                    ->withBodyKey('pickup_lng', $body['pickup_lng'] ?? null)
                    ->withBodyKey('cost', $body['cost'] ?? null)
                    ->withBodyKey('ride_id', $body['rideId'] ?? null)
                    ->withBodyKey('request_id', $request_id)
                    ->send();

                Location::create([
                    'request_id' => $request_id,
                    'pickup_location' => $pickupLocation,
                    'dropoff_location' => $dropoffLocation,
                    'dropoff_lng' => $body['dropoff_lng'],
                    'dropoff_lat' => $body['dropoff_lat'],
                    'pickup_lat' => $body['pickup_lat'],
                    'pickup_lng' => $body['pickup_lng'],
                ]);

                // Log the valid ride request
                $this->info("Valid ride request received:");
                $this->info("Pickup Location: {$pickupLocation}");
                $this->info("Dropoff Location: {$dropoffLocation}");

            })
            ->build()
            ->consume();
    }
}
