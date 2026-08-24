<?php

namespace App\Console\Commands;

use App\Services\NearbyDriversService;
use EventHandler;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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

        $consumer = Kafka::consumer(['x'])
            ->withConsumerGroupId('test-group')
            ->withHandler(function (ConsumerMessage $message) {
                $this->info('MESSAGE RECEIVED');

                $this->info(json_encode($message->getBody()));

                Log::info('Kafka message received', [
                    'body' => $message->getBody(),
                    'topic' => $message->getTopicName(),
                    'partition' => $message->getPartition(),
                    'offset' => $message->getOffset(),
                ]);
            })
            ->build();

        $this->info('Consumer started. Waiting for messages...');

        //find near by drivers
        $nearbyDrivers = app(NearbyDriversService::class)->nearbyDrivers(
            51.5074, // Example longitude
            -0.1278, // Example latitude
            5,       // Radius in kilometers
            20       // Limit of results
        );

        //call notification service to send notification to nearby drivers

        $consumer->consume();
    }
}