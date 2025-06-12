<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liker_solar_system', function (Blueprint $table) {
            $table->foreignId('solar_system_id')->references('solar_system_id')->on('solar_system');
            $table->foreignId('user_id')->references('user_id')->on('user');
            $table->timestamp('liker_solar_system_date')->useCurrent();
            
            $table->primary(['solar_system_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liker_solar_system');
    }
};
