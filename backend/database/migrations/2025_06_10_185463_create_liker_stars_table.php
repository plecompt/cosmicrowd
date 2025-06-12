<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liker_star', function (Blueprint $table) {
            $table->datetime('liker_star_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('star_id');

            $table->foreign('user_id')->references('user_id')->on('user');
            $table->foreign('star_id')->references('star_id')->on('star');
            
            // Clé primaire composite
            $table->primary(['star_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liker_star');
    }
};
