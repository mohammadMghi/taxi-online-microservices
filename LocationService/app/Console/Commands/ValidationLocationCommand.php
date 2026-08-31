<?php

namespace App\Console\Commands;

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

                // Validate locations
                if (!$pickupLocation || !$dropoffLocation) {
                    $this->error('Invalid ride request: Missing pickup or dropoff location.');
                    return;
                }

                Kafka::publish()
                    ->onTopic('nearby-drivers-found')
                    ->withBodyKey('userId', $body['userId'])
                    ->withBodyKey('pickup_location', $pickupLocation)
                    ->withBodyKey('dropoff_location', $dropoffLocation)
                    ->send();

                // Log the valid ride request
                $this->info("Valid ride request received:");
                $this->info("Pickup Location: {$pickupLocation}");
                $this->info("Dropoff Location: {$dropoffLocation}");

            })
            ->build()
            ->consume();
    }
}
