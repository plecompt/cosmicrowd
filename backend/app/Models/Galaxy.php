<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

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
        'galaxy_age',
    ];

    protected $casts = [
        'galaxy_size' => 'integer',
        'galaxy_age' => 'integer',
    ];

    /**
     * Get all solar systems belonging to this galaxy.
     *
     * @return HasMany
     */
    public function solarSystems(): HasMany
    {
        return $this->hasMany(SolarSystem::class, 'galaxy_id');
    }

    /**
     * Get the count of solar systems in this galaxy.
     *
     * @return int
     */
    public function solarSystemsCount(): int
    {
        return $this->solarSystems()->count();
    }

    /**
     * Get all planets in this galaxy via solar systems.
     *
     * @return HasManyThrough
     */
    public function planets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Planet::class, 
            SolarSystem::class, 
            'galaxy_id',      // FK on SolarSystem referencing Galaxy
            'solar_system_id',// FK on Planet referencing SolarSystem
            'galaxy_id',      // Local PK on Galaxy
            'solar_system_id' // Local PK on SolarSystem
        );
    }

    /**
     * Get all moons in this galaxy by filtering moons via planets and solar systems.
     *
     * @return Builder
     */
    public function moons(): Builder
    {
        return Moon::whereHas('planet.solarSystem', function ($query): void {
            $query->where('galaxy_id', $this->galaxy_id);
        });
    }

    /**
     * Get total count of solar systems, planets, and moons in this galaxy.
     *
     * @return int
     */
    public function getTotalObjectsCount(): int
    {
        return $this->solarSystemsCount() + $this->planets()->count() + $this->moons()->count();
    }
}
