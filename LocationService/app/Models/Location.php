<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'request_id',
    'pickup_location',
    'dropoff_location',
    'dropoff_lng',
    'dropoff_lat',
    'pickup_lat',
    'pickup_lng'
])]
class Location extends Model
{
    //
}
