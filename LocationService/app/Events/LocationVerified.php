<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $userId,
        public string $lat,
        public string $long,
    ) {}
}