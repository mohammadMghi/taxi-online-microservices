<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'driver_id', 
    'status',
    'pickup_location',
    'dropoff_location',
    'dropoff_lat',
    'dropoff_lng',
    'pickup_lat',
    'pickup_lng']
)]
class Ride extends Model
{
    //
}
