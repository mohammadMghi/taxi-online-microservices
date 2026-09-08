<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

#[Signature('kafka:deduct-mony-command')]
#[Description('Command description')]
class DeductMonyCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Kafka::consumer(['ride-completed'])
            ->withConsumerGroupId('deduct-mony-command-group')
            ->withHandler(function ($message) {
                $body = $message->getBody();
                
                $cost = $body['cost'] ?? null;
                $userId = $body['userId'] ?? null;
                $rideId = $body['rideId'] ?? null; 

                $user = User::find($userId);

                $newUserBalance = $user->balance - $cost;
  
                if ($newUserBalance < 0) {
                    $this->error("User ID: {$userId} has insufficient balance for ride ID: {$rideId}");
                    
                    Kafka::publish()
                        ->onTopic('insufficient-balance')
                        ->withBodyKey('userId', $userId)
                        ->withBodyKey('rideId', $rideId)
                        ->withBodyKey('cost', $cost)
                        ->send();

                    return;
                }

                $user->save();

                // Here you would implement the logic to deduct money from the user's account.
                // For example, you might call a service or update a database record.

                // Log the deduction for demonstration purposes
                $this->info("Deducting money for user ID: {$userId} for ride ID: {$rideId}");
            })
            ->build()
            ->consume();
    }
}
