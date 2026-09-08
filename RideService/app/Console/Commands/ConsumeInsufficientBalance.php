<?php

namespace App\Console\Commands;

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
                
                $userId = $body['userId'] ?? null;
                $rideId = $body['rideId'] ?? null;

                $amount = $body['amount'] ?? null;

                if (!$userId || !$rideId || !$amount) {
                    $this->error('Invalid insufficient balance message: Missing userId or rideId or amount.');
                    return;
                } 

                $invoice = \App\Models\Invoice::where('ride_id', $rideId)->first();

                $invoice->ride_id = $rideId;

                $invoice->user_id = $userId;

                $invoice->amount = $amount;

                $invoice->status = 'not-paid';

                $invoice->save();

                $this->info("User ID: {$userId} has insufficient balance for ride ID: {$rideId}");
            })
            ->build()
            ->consume();
    }
}
