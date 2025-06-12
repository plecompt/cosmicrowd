<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liker_moon', function (Blueprint $table) {
            $table->foreignId('moon_id')->references('moon_id')->on('moon');
            $table->foreignId('user_id')->references('user_id')->on('user');
            $table->timestamp('liker_moon_date')->useCurrent();
            
            $table->primary(['moon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liker_moon');
    }
};
