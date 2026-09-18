<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('request_id');
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->integer('dropoff_lng');
            $table->integer('dropoff_lat');
            $table->integer('pickup_lat');
            $table->integer('pickup_lng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
