<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallpaper', function (Blueprint $table) {
            $table->id('wallpaper_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('galaxy_id');
            $table->unsignedBigInteger('solar_system_id');
            $table->text('wallpaper_settings');
            $table->timestamp('wallpaper_created_at')->useCurrent();
            
            $table->foreign('user_id')->references('user_id')->on('user');
            $table->foreign('galaxy_id')->references('galaxy_id')->on('galaxy');
            $table->foreign('solar_system_id')->references('solar_system_id')->on('solar_system');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallpaper');
    }
};