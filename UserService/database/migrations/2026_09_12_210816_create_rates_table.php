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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('ride_id');

            $table->foreignId('rater_id');
            $table->foreignId('ratee_id');

            $table->unsignedTinyInteger('rating');

            $table->timestamps();

            $table->unique(['ride_id', 'rater_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
