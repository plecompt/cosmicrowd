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
        'planet_gravity' => 'float',
        'planet_surface_temp' => 'float',
        'planet_orbital_longitude' => 'float',
        'planet_eccentricity' => 'float',
        'planet_apogee' => 'integer',
        'planet_perigee' => 'integer',
        'planet_orbital_inclination' => 'integer',
        'planet_average_distance' => 'integer',
        'planet_orbital_period' => 'integer',
        'planet_inclination_angle' => 'integer',
        'planet_rotation_period' => 'integer',
        'planet_mass' => 'integer',
        'planet_diameter' => 'integer',
        'planet_rings' => 'integer',
        'planet_initial_x' => 'integer',
        'planet_initial_y' => 'integer',
        'planet_initial_z' => 'integer'
    ];

    // ========== RELATIONS PRINCIPALES ==========
    
    // Relation avec l'utilisateur créateur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relation avec le système solaire parent
    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'solar_system_id');
    }

    // Relation avec les lunes
    public function moons()
    {
        return $this->hasMany(Moon::class, 'planet_id', 'planet_id');
    }

    // ========== RELATIONS LIKES ==========
    
    // Relation directe avec la table de likes
    public function likes()
    {
        return $this->hasMany(LikePlanet::class, 'planet_id', 'planet_id');
    }

    // Utilisateurs qui ont liké cette planète
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'like_planet', 'planet_id', 'user_id')
                    ->withPivot('like_planet_date')
                    ->orderByPivot('like_planet_date', 'desc');
    }

    // ========== MÉTHODES UTILES ==========

    public function getLikesCount()
    {
        return $this->likes()->count();
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function getMoonsCount()
    {
        return $this->moons()->count();
    }

    public function getSystemName()
    {
        return $this->solarSystem->solar_system_name;
    }

    public function getGalaxyName()
    {
        return $this->solarSystem->galaxy->galaxy_name;
    }

    public function getFullPath()
    {
        return $this->getGalaxyName() . ' > ' . $this->getSystemName() . ' > ' . $this->planet_name;
    }

    // ========== SCOPES ==========

    // Scope pour les planètes les plus populaires
    public static function scopePopular($query, $limit = 10)
    {
        return $query->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit);
    }

    public static function scopeByType($query, $type)
    {
        return $query->where('planet_type', $type);
    }

    public static function scopeWithLikesStats($query)
    {
        return $query->withCount(['likes', 'moons' => function ($q) {
            $q->withCount('likes');
        }]);
    }

    public static function scopeHabitable($query)
    {
        return $query->whereIn('planet_type', ['terrestrial', 'ocean']);
    }

    public static function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public static function scopeInSystem($query, $solarSystemId)
    {
        return $query->where('solar_system_id', $solarSystemId);
    }
}