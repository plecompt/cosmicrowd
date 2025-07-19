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
            $table->bigInteger('moon_apogee')->unsigned();
            $table->bigInteger('moon_perigee')->unsigned();
            $table->integer('moon_orbital_inclination')->unsigned();
            $table->bigInteger('moon_average_distance')->unsigned();
            $table->integer('moon_orbital_period')->unsigned();
            $table->integer('moon_inclination_angle')->unsigned();
            $table->integer('moon_rotation_period')->unsigned();
            $table->float('moon_mass')->unsigned(); // x 10^24kg
            $table->bigInteger('moon_diameter')->unsigned();
            $table->integer('moon_rings')->unsigned();
            $table->integer('moon_initial_x');
            $table->integer('moon_initial_y');
            $table->integer('moon_initial_z');
            $table->foreignId('planet_id')->references('planet_id')->on('planet');
            $table->foreignId('user_id')->nullable()->references('user_id')->on('user')->onDelete('set null');
        });

        // Adding check constraints based on realistic astronomical limits
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_gravity CHECK (moon_gravity >= 0 AND moon_gravity <= 25)'); //0-25 m.s² max
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_surface_temp CHECK (moon_surface_temp >= 0 AND moon_surface_temp <= 700)'); // 0 to 700 K
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_orbital_longitude CHECK (moon_orbital_longitude >= 0 AND moon_orbital_longitude <= 360)'); //0-360°
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_eccentricity CHECK (moon_eccentricity >= 0 AND moon_eccentricity <= 1)'); //0-1
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_apogee CHECK (moon_apogee >= 100 AND moon_apogee <= 10000000)'); //0-10000000 km max
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_perigee CHECK (moon_perigee >= 100 AND moon_perigee <= 10000000)'); //0-10000000 km max
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_orbital_inclination CHECK (moon_orbital_inclination >= 0 AND moon_orbital_inclination <= 360)'); //0-360°
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_orbital_period CHECK (moon_orbital_period >= 1 AND moon_orbital_period <= 10000)'); //10000 days max (27 years)
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_inclination_angle CHECK (moon_inclination_angle >= 0 AND moon_inclination_angle <= 360)'); //0-360°
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_rotation_period CHECK (moon_rotation_period >= 1 AND moon_rotation_period <= 2000)'); //2000 hours max (83 days)
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_mass CHECK (moon_mass >= 0 AND moon_mass <= 1000)'); //0-1000 x10^24 kg
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_diameter CHECK (moon_diameter >= 0 AND moon_diameter <= 10000)'); // 1-10000km max 
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_rings CHECK (moon_rings >= 0 AND moon_rings <= 10)'); //0-10
        DB::statement('ALTER TABLE moon ADD CONSTRAINT check_moon_perigee_apogee CHECK (moon_perigee <= moon_apogee)'); //perigee <= apogee
    }

    public function down(): void
    {
        Schema::dropIfExists('moon');
    }
};
