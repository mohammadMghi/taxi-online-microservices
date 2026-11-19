<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('topic' , 'message' , 'acks' , 'published_at')]
class OutboxMessage extends Model
{
    //
}
