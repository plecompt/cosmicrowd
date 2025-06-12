<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galaxy', function (Blueprint $table) {
            $table->id('galaxy_id');
            $table->integer('galaxy_size')->unsigned();
            $table->string('galaxy_name', 50);
            $table->string('galaxy_desc', 255)->nullable();
            $table->integer('galaxy_age')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galaxy');
    }
};