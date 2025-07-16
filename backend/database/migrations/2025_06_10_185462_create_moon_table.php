<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moon', function (Blueprint $table) {
            $table->id('moon_id');
            $table->string('moon_desc', 255)->nullable();
            $table->string('moon_name', 50);
            $table->enum('moon_type', ['rocky', 'icy', 'mixed', 'primitive', 'regular', 'irregular', 'trojan', 'coorbital']);
            $table->float('moon_gravity')->unsigned();
            $table->float('moon_surface_temp');
            $table->float('moon_orbital_longitude')->unsigned();
            $table->float('moon_eccentricity')->unsigned();
            $table->integer('moon_apogee')->unsigned();
            $table->integer('moon_perigee')->unsigned();
            $table->integer('moon_orbital_inclination')->unsigned();
            $table->bigInteger('moon_average_distance')->unsigned();
            $table->integer('moon_orbital_period')->unsigned();
            $table->integer('moon_inclination_angle')->unsigned();
            $table->integer('moon_rotation_period')->unsigned();
            $table->bigInteger('moon_mass')->unsigned(); // x 10^24kg
            $table->bigInteger('moon_diameter')->unsigned();
            $table->integer('moon_rings')->unsigned();
            $table->integer('moon_initial_x');
            $table->integer('moon_initial_y');
            $table->integer('moon_initial_z');
            $table->foreignId('planet_id')->references('planet_id')->on('planet');
            $table->foreignId('user_id')->nullable()->references('user_id')->on('user')->onDelete('set null');
        });

        // Adding check constrains
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_gravity CHECK (moon_gravity >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_surface_temp CHECK (moon_surface_temp >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_orbital_longitude CHECK (moon_orbital_longitude >= 0 AND moon_orbital_longitude <= 360)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_eccentricity CHECK (moon_eccentricity >= 0 AND moon_eccentricity <= 1)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_apogee CHECK (moon_apogee >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_perigee CHECK (moon_perigee >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_orbital_inclination CHECK (moon_orbital_inclination >= 0 AND moon_orbital_inclination <= 360)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_average_distance CHECK (moon_average_distance >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_orbital_period CHECK (moon_orbital_period >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_inclination_angle CHECK (moon_inclination_angle >= 0 AND moon_inclination_angle <= 360)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_rotation_period CHECK (moon_rotation_period >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_mass CHECK (moon_mass >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_diameter CHECK (moon_diameter >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_rings CHECK (moon_rings >= 0)');
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_perigee_apogee CHECK (moon_perigee <= moon_apogee)');
    }

    public function down(): void
    {
        Schema::dropIfExists('moon');
    }
};
