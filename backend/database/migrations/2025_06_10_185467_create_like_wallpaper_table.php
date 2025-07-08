<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('like_wallpaper', function (Blueprint $table) {
            $table->foreignId('wallpaper_id')->references('wallpaper_id')->on('wallpaper');
            $table->foreignId('user_id')->references('user_id')->on('user');
            $table->timestamp('like_wallpaper_date')->useCurrent();
            
            $table->primary(['wallpaper_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('like_wallpaper');
    }
};
