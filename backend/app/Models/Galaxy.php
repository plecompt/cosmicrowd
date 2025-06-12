<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Galaxy extends Model
{
    use HasFactory;

    protected $table = 'galaxy';
    protected $primaryKey = 'galaxy_id';
    public $timestamps = false;

    protected $fillable = [
        'galaxy_name',
        'galaxy_desc',
        'galaxy_size',
        'galaxy_age'
    ];

    protected $casts = [
        'galaxy_size' => 'integer',
        'galaxy_age' => 'integer',
    ];

    // Relations
    public function solarSystems(): HasMany
    {
        return $this->hasMany(SolarSystem::class, 'galaxy_id');
    }

    // Méthodes utiles
    public function solarSystemsCount()
    {
        return $this->solarSystems()->count();
    }

    // public function activeSolarSystemsCount()
    // {
    //     return $this->solarSystems()->whereHas('user', function ($query) {
    //         $query->where('user_active', true);
    //     })->count();
    // }

    // Obtenir toutes les planètes de la galaxie
    public function planets()
    {
        return $this->hasManyThrough(Planet::class, SolarSystem::class, 'galaxy_id', 'solar_system_id', 'galaxy_id', 'solar_system_id');
    }

    // Obtenir toutes les lunes de la galaxie
    public function moons()
    {
        return Moon::whereHas('planet.solarSystem', function($query) {
            $query->where('galaxy_id', $this->galaxy_id);
        });
    }

    // Statistiques de la galaxie
    public function getTotalObjectsCount()
    {
        return $this->solarSystemsCount() + $this->planets()->count() + $this->moons()->count();
    }
}
