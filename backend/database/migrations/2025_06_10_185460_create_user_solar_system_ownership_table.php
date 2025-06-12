<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_solar_system_ownership', function (Blueprint $table) {
            $table->foreignId('user_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreignId('solar_system_id')->references('solar_system_id')->on('solar_system')->onDelete('cascade');
            $table->enum('ownership_type', ['claimed', 'created']);
            $table->timestamp('owned_at')->useCurrent();
            
            $table->primary(['user_id', 'solar_system_id']);
            $table->unique('solar_system_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_solar_system_ownership');
    }
}; 