<?php

namespace App\Models;

use App\Models\LikeSolarSystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarSystem extends Model
{
    use HasFactory;

    protected $table = 'solar_system';
    protected $primaryKey = 'solar_system_id';
    public $timestamps = false;

    protected $fillable = [
        'galaxy_id',
        'user_id',
        'solar_system_name',
        'solar_system_desc',
        'solar_system_type',
        'solar_system_gravity',
        'solar_system_surface_temp',
        'solar_system_diameter',
        'solar_system_mass',
        'solar_system_luminosity',
        'solar_system_initial_x',
        'solar_system_initial_y',
        'solar_system_initial_z'
    ];

    protected $casts = [
        'solar_system_mass' => 'integer'
    ];

    // Relations
    public function galaxy()
    {
        return $this->belongsTo(Galaxy::class, 'galaxy_id', 'galaxy_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planets()
    {
        return $this->hasMany(Planet::class, 'solar_system_id', 'solar_system_id');
    }

    public function likes()
    {
        return $this->hasMany(LikeSolarSystem::class, 'solar_system_id', 'solar_system_id');
    }
}
