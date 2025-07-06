<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planet', function (Blueprint $table) {
            $table->id('planet_id');
            $table->string('planet_desc', 255)->nullable();
            $table->string('planet_name', 50);
            $table->enum('planet_type', [
                'terrestrial', 'gas', 'ice', 'super_earth',
                'sub_neptune', 'dwarf', 'lava', 'carbon', 'ocean'
            ]);
            $table->float('planet_gravity')->unsigned();
            $table->float('planet_surface_temp');
            $table->float('planet_orbital_longitude')->unsigned();
            $table->float('planet_eccentricity')->unsigned();
            $table->integer('planet_apogee')->unsigned();
            $table->integer('planet_perigee')->unsigned();
            $table->integer('planet_orbital_inclination')->unsigned();
            $table->bigInteger('planet_average_distance')->unsigned();
            $table->integer('planet_orbital_period')->unsigned();
            $table->integer('planet_inclination_angle')->unsigned();
            $table->integer('planet_rotation_period')->unsigned();
            $table->bigInteger('planet_mass')->unsigned();
            $table->integer('planet_diameter')->unsigned();
            $table->integer('planet_rings')->unsigned();
            $table->integer('planet_initial_x');
            $table->integer('planet_initial_y');
            $table->integer('planet_initial_z');
            $table->foreignId('solar_system_id')->references('solar_system_id')->on('solar_system');
            $table->foreignId('user_id')->references('user_id')->on('user');
        });

        // Adding check constrains
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_gravity CHECK (planet_gravity >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_surface_temp CHECK (planet_surface_temp >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_orbital_longitude CHECK (planet_orbital_longitude >= 0 AND planet_orbital_longitude <= 360)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_eccentricity CHECK (planet_eccentricity >= 0 AND planet_eccentricity <= 1)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_apogee CHECK (planet_apogee >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_perigee CHECK (planet_perigee >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_orbital_inclination CHECK (planet_orbital_inclination >= 0 AND planet_orbital_inclination <= 360)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_average_distance CHECK (planet_average_distance >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_orbital_period CHECK (planet_orbital_period >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_inclination_angle CHECK (planet_inclination_angle >= 0 AND planet_inclination_angle <= 360)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_rotation_period CHECK (planet_rotation_period >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_mass CHECK (planet_mass >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_diameter CHECK (planet_diameter >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_rings CHECK (planet_rings >= 0)');
        DB::statement('ALTER TABLE planet ADD CONSTRAINT check_planet_perigee_apogee CHECK (planet_perigee <= planet_apogee)');
    }

    public function down(): void
    {
        Schema::dropIfExists('planet');
    }
};
