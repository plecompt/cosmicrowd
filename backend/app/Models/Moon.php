<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasOneThrough,
    HasMany,
    BelongsToMany
};

class Moon extends Model
{
    use HasFactory;

    protected $table = 'moon';
    protected $primaryKey = 'moon_id';
    public $timestamps = false;

    protected $fillable = [
        'moon_desc',
        'moon_name',
        'moon_type',
        'moon_gravity',
        'moon_surface_temp',
        'moon_orbital_longitude',
        'moon_eccentricity',
        'moon_apogee',
        'moon_perigee',
        'moon_orbital_inclination',
        'moon_average_distance',
        'moon_orbital_period',
        'moon_inclination_angle',
        'moon_rotation_period',
        'moon_mass',
        'moon_diameter',
        'moon_rings',
        'moon_initial_x',
        'moon_initial_y',
        'moon_initial_z',
        'planet_id',
        'user_id',
    ];

    protected $casts = [
        'moon_average_distance' => 'integer',
        'moon_mass' => 'integer',
    ];

    /**
     * Relation: Moon belongs to User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relation: Moon belongs to Planet.
     *
     * @return BelongsTo
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id');
    }

    /**
     * Relation: Moon has one SolarSystem through Planet.
     *
     * @return HasOneThrough
     */
    public function solarSystem(): HasOneThrough
    {
        return $this->hasOneThrough(
            SolarSystem::class,
            Planet::class,
            'planet_id',        // Foreign key on Planet table
            'solar_system_id',  // Foreign key on SolarSystem table
            'planet_id',        // Local key on Moon table
            'solar_system_id'   // Local key on Planet table
        );
    }

    /**
     * Relation: Moon has many LikeMoon.
     *
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(LikeMoon::class, 'moon_id', 'moon_id');
    }

    /**
     * Relation: Moon liked by many Users (pivot table like_moon).
     *
     * @return BelongsToMany
     */
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'like_moon', 'moon_id', 'user_id');
    }
}
