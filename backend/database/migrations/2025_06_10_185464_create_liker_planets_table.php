<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liker_planet', function (Blueprint $table) {
            $table->unsignedBigInteger('planet_id');
            $table->unsignedBigInteger('user_id');
            $table->datetime('liker_planet_date');

            $table->primary(['planet_id', 'user_id']);
            
            $table->foreign('planet_id')->references('planet_id')->on('planet');
            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liker_planet');
    }
};