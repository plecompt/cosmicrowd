<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galaxy extends Model
{
    use HasFactory;

    protected $table = 'galaxy';
    protected $primaryKey = 'galaxy_id';
    public $timestamps = false;

    protected $fillable = [
        'galaxy_size',
        'galaxy_name',
        'galaxy_desc',
        'galaxy_age',
    ];

    protected $casts = [
        'galaxy_size' => 'integer',
        'galaxy_age' => 'integer',
    ];

    // Relations
    public function stars()
    {
        return $this->hasMany(Star::class, 'galaxy_id', 'galaxy_id');
    }

    // Méthodes utiles
    public function starsCount()
    {
        return $this->stars()->count();
    }

    public function activeStarsCount()
    {
        return $this->stars()->whereHas('user', function ($query) {
            $query->where('user_active', true);
        })->count();
    }

    // Obtenir toutes les planètes de la galaxie
    public function planets()
    {
        return $this->hasManyThrough(Planet::class, Star::class, 'galaxy_id', 'star_id', 'galaxy_id', 'star_id');
    }

    // Obtenir toutes les lunes de la galaxie
    public function moons()
    {
        return Moon::whereHas('planet.star', function($query) {
            $query->where('galaxy_id', $this->galaxy_id);
        });
    }

    // Statistiques de la galaxie
    public function getTotalObjectsCount()
    {
        return $this->starsCount() + $this->planets()->count() + $this->moons()->count();
    }
}
