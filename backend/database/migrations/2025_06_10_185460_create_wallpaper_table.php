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
            $table->text('wallpaper_settings');
            $table->timestamp('wallpaper_created_at')->useCurrent();
            
            $table->foreignId('user_id')->constrained('user', 'user_id');
            $table->foreignId('galaxy_id')->constrained('galaxy', 'galaxy_id');
            $table->foreignId('solar_system_id')->constrained('solar_system', 'solar_system_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallpaper');
    }
};