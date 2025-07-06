<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'user_id'
    ];

    protected $casts = [
        'moon_average_distance' => 'integer',
        'moon_mass' => 'integer'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planet()
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id');
    }

    public function likes()
    {
        return $this->hasMany(LikeMoon::class, 'moon_id', 'moon_id');
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'like_moon', 'moon_id', 'user_id');
    }
}