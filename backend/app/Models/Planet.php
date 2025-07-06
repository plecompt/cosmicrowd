<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planet extends Model
{
    use HasFactory;

    protected $table = 'planet';
    protected $primaryKey = 'planet_id';
    public $timestamps = false;

    protected $fillable = [
        'planet_desc',
        'planet_name',
        'planet_type',
        'planet_gravity',
        'planet_surface_temp',
        'planet_orbital_longitude',
        'planet_eccentricity',
        'planet_apogee',
        'planet_perigee',
        'planet_orbital_inclination',
        'planet_average_distance',
        'planet_orbital_period',
        'planet_inclination_angle',
        'planet_rotation_period',
        'planet_mass',
        'planet_diameter',
        'planet_rings',
        'planet_initial_x',
        'planet_initial_y',
        'planet_initial_z',
        'solar_system_id',
        'user_id'
    ];

    protected $casts = [
        'planet_average_distance' => 'integer',
        'planet_mass' => 'integer'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'solar_system_id');
    }

    public function moons()
    {
        return $this->hasMany(Moon::class, 'planet_id', 'planet_id');
    }

    public function likes()
    {
        return $this->hasMany(LikePlanet::class, 'planet_id', 'planet_id');
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'like_planet', 'planet_id', 'user_id');
    }
}