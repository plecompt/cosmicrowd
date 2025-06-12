<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planet', function (Blueprint $table) {
            $table->id('planet_id');
            $table->string('planet_desc', 255)->nullable();
            $table->string('planet_name', 50);
            $table->enum('planet_type', [
                'terrestrial',
                'gas',
                'ice',
                'super_earth',
                'sub_neptune',
                'dwarf',
                'lava',
                'carbon',
                'ocean'
            ]);
            $table->float('planet_gravity')->unsigned();
            $table->float('planet_surface_temp');
            $table->float('planet_orbital_longitude');
            $table->float('planet_eccentricity');
            $table->integer('planet_apogee')->unsigned();
            $table->integer('planet_perigee')->unsigned();
            $table->integer('planet_orbital_inclination');
            $table->bigInteger('planet_average_distance')->unsigned();
            $table->integer('planet_orbital_period')->unsigned();
            $table->integer('planet_inclination_angle');
            $table->integer('planet_rotation_period')->unsigned();
            $table->bigInteger('planet_mass')->unsigned();
            $table->integer('planet_diameter')->unsigned();
            $table->integer('planet_rings')->unsigned();
            $table->integer('planet_initial_x');
            $table->integer('planet_initial_y');
            $table->integer('planet_initial_z');
            $table->unsignedBigInteger('star_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('star_id')->references('star_id')->on('star');
            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planet');
    }
};
