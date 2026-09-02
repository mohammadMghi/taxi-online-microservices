<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
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
 

                $this->info("Notification sent to driver: " . json_encode($body['driverId']));
            })
            ->build()
            ->consume();
    }
}