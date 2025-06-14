<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_system', function (Blueprint $table) {
            $table->id('solar_system_id');
            $table->string('solar_system_name', 50);
            $table->string('solar_system_desc', 255)->nullable();
            $table->enum('solar_system_type', [
                'brown_dwarf', 'red_dwarf', 'yellow_dwarf', 'white_dwarf',
                'red_giant', 'blue_giant', 'red_supergiant', 'blue_supergiant',
                'hypergiant', 'neutron_star', 'pulsar', 'variable',
                'binary', 'ternary', 'black_hole'
            ]);
            $table->float('solar_system_gravity')->unsigned();
            $table->float('solar_system_surface_temp');
            $table->integer('solar_system_diameter')->unsigned();
            $table->bigInteger('solar_system_mass')->unsigned();
            $table->integer('solar_system_luminosity')->unsigned();
            $table->integer('solar_system_initial_x');
            $table->integer('solar_system_initial_y');
            $table->integer('solar_system_initial_z');
            $table->foreignId('galaxy_id')->references('galaxy_id')->on('galaxy');
        });

        // Ajout des contraintes CHECK pour MariaDB
        DB::statement('ALTER TABLE solar_system ADD CONSTRAINT check_solar_system_gravity CHECK (solar_system_gravity >= 0)');
        DB::statement('ALTER TABLE solar_system ADD CONSTRAINT check_solar_system_surface_temp CHECK (solar_system_surface_temp >= 0)');
        DB::statement('ALTER TABLE solar_system ADD CONSTRAINT check_solar_system_diameter CHECK (solar_system_diameter >= 0)');
        DB::statement('ALTER TABLE solar_system ADD CONSTRAINT check_solar_system_mass CHECK (solar_system_mass >= 0)');
        DB::statement('ALTER TABLE solar_system ADD CONSTRAINT check_solar_system_luminosity CHECK (solar_system_luminosity >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_system');
    }
};