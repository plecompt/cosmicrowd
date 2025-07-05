<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('like_planet', function (Blueprint $table) {
            $table->foreignId('planet_id')->references('planet_id')->on('planet');
            $table->foreignId('user_id')->references('user_id')->on('user');
            $table->timestamp('like_planet_date')->useCurrent();
            
            $table->primary(['planet_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('like_planet');
    }
};
