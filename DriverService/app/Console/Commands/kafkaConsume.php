<?php

namespace App\Console\Commands;
 
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

                // $nearbyDrivers = app(NearbyDriversService::class)->nearbyDrivers(
                //     $dropoff_location,
                //     $pickup_location,
                //     5,
                //     20
                // );

                // $this->info('Nearby Drivers: ' . json_encode($nearbyDrivers));

                // Kafka::publish()
                //     ->onTopic('ride-request-notification')
                //     ->withBodyKey('nearby_drivers', $nearbyDrivers)
                //     ->send();

                // Log::info('Kafka message received', [
                //     'body' => $message->getBody(),
                //     'topic' => $message->getTopicName(),
                //     'partition' => $message->getPartition(),
                //     'offset' => $message->getOffset(),
                // ]);
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