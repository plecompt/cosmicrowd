<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liker_moon', function (Blueprint $table) {
            $table->unsignedBigInteger('moon_id');
            $table->unsignedBigInteger('user_id');
            $table->datetime('liker_moon_date');

            $table->primary(['moon_id', 'user_id']);
            
            $table->foreign('moon_id')->references('moon_id')->on('moon');
            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liker_moon');
    }
};
