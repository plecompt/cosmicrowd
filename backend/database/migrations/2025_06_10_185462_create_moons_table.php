<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moon', function (Blueprint $table) {
            $table->id('moon_id');
            $table->string('moon_desc', 255)->nullable();
            $table->string('moon_name', 50);
            $table->enum('moon_type', [
                'rocky',
                'icy',
                'mixed',
                'primitive',
                'regular',
                'irregular',
                'trojan',
                'coorbital'
            ]);
            $table->float('moon_gravity')->unsigned();
            $table->float('moon_surface_temp');
            $table->float('moon_orbital_longitude');
            $table->float('moon_eccentricity');
            $table->integer('moon_apogee')->unsigned();
            $table->integer('moon_perigee')->unsigned();
            $table->integer('moon_orbital_inclination');
            $table->bigInteger('moon_average_distance')->unsigned();
            $table->integer('moon_orbital_period')->unsigned();
            $table->integer('moon_inclination_angle');
            $table->integer('moon_rotation_period')->unsigned();
            $table->bigInteger('moon_mass')->unsigned();
            $table->integer('moon_diameter')->unsigned();
            $table->integer('moon_rings')->unsigned();
            $table->integer('moon_initial_x');
            $table->integer('moon_initial_y');
            $table->integer('moon_initial_z');
            $table->unsignedBigInteger('planet_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('planet_id')->references('planet_id')->on('planet');
            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moon');
    }
};
