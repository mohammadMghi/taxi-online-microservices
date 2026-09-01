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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('driver_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->integer('dropoff_lat')->nullable();
            $table->integer('dropoff_lng')->nullable();
            $table->integer('pickup_lat')->nullable();
            $table->integer('pickup_lng')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
