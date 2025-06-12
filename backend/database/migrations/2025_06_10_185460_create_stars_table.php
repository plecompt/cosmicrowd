<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('star', function (Blueprint $table) {
            $table->id('star_id');
            $table->string('star_desc', 255)->nullable();
            $table->string('star_name', 50);
            $table->enum('star_type', [
                'brown_dwarf', 
                'red_dwarf', 
                'yellow_dwarf', 
                'white_dwarf', 
                'red_giant', 
                'blue_giant',
                'red_supergiant', 
                'blue_supergiant',
                'hypergiant', 
                'neutron_star', 
                'pulsar', 
                'variable', 
                'binary', 
                'ternary'
            ]);
            $table->float('star_gravity')->unsigned();
            $table->float('star_surface_temp');
            $table->integer('star_diameter')->unsigned();
            $table->bigInteger('star_mass')->unsigned();
            $table->integer('star_luminosity')->unsigned();
            $table->integer('star_initial_x');
            $table->integer('star_initial_y');
            $table->integer('star_initial_z');
            $table->unsignedBigInteger('galaxy_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('galaxy_id')->references('galaxy_id')->on('galaxy');
            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('star');
    }
};
