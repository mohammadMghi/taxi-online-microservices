<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Ride;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:consume-insufficient-balance')]
#[Description('Command description')]
class ConsumeInsufficientBalance extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Kafka::consumer(['insufficient-balance'])
            ->withConsumerGroupId('ride-service-balance-group')
            ->withHandler(function ($message) {
                $body = $message->getBody();
                
                $userId = (int) $body['userId'] ?? null;
                $rideId = (int) $body['rideId'] ?? null; 
                $cost = $body['cost'] ?? null;

                if (!$userId || !$rideId || !$cost) {
                    $this->error('Invalid insufficient balance message: Missing userId or rideId or cost.');
                    return;
                } 

                $ride = Ride::find($rideId);

                $invoice = new Invoice();

                $invoice->ride_id = $rideId;

                $invoice->driver_id = $ride->driver_id;

                $invoice->user_id = $userId;

                $invoice->amount = $cost;

                $invoice->status = 'not-paid';

                $invoice->save();

                $this->info("User ID: {$userId} has insufficient balance for ride ID: {$rideId}");
            })
            ->build()
            ->consume();
    }
}
