<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasMany,
    BelongsToMany
};

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
        'user_id',
    ];

    protected $casts = [
        'planet_average_distance' => 'integer',
        'planet_mass' => 'integer',
    ];

    /**
     * Relation: Planet belongs to User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relation: Planet belongs to SolarSystem.
     *
     * @return BelongsTo
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'solar_system_id');
    }

    /**
     * Relation: Planet has many Moons.
     *
     * @return HasMany
     */
    public function moons(): HasMany
    {
        return $this->hasMany(Moon::class, 'planet_id', 'planet_id');
    }

    /**
     * Relation: Planet has many LikePlanet.
     *
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(LikePlanet::class, 'planet_id', 'planet_id');
    }

    /**
     * Relation: Planet liked by many Users (pivot table like_planet).
     *
     * @return BelongsToMany
     */
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'like_planet', 'planet_id', 'user_id');
    }
}
