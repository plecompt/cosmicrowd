<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    // Get the number of SolarSystem in this galaxy
    public function solarSystemsCount()
    {
        return $this->solarSystems()->count();
    }

    // Get all the planets in this galaxy, having to go through Galaxy -> SolarSystem -> Planet
    public function planets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Planet::class, // Final model we want to get
            SolarSystem::class, // Intermediate model we are passing through
            'galaxy_id', // FK in SolarSystem is referencing in Galaxy Table
            'solar_system_id', // FK in planet which is referencing in SolarSystem Table  
            'galaxy_id', // PK local in Galaxy
            'solar_system_id'); // PK in SolarSystem
    }

    // Get all the moons in this galaxy
    public function moons()
    {
        return Moon::whereHas('planet.solarSystem', function ($query) {
            $query->where('galaxy_id', $this->galaxy_id);
        });
    }


    // Global count of this galaxy
    public function getTotalObjectsCount()
    {
        return $this->solarSystemsCount() + $this->planets()->count() + $this->moons()->count();
    }
}
