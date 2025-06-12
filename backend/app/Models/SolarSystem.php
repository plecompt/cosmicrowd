<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarSystem extends Model
{
    use HasFactory;

    protected $table = 'solar_system';
    protected $primaryKey = 'solar_system_id';
    public $timestamps = false;

    protected $fillable = [
        'solar_system_desc',
        'solar_system_name',
        'solar_system_type',
        'solar_system_gravity',
        'solar_system_surface_temp',
        'solar_system_diameter',
        'solar_system_mass',
        'solar_system_luminosity',
        'solar_system_initial_x',
        'solar_system_initial_y',
        'solar_system_initial_z',
        'galaxy_id'
    ];

    protected $casts = [
        'solar_system_gravity' => 'float',
        'solar_system_surface_temp' => 'float',
        'solar_system_diameter' => 'integer',
        'solar_system_mass' => 'integer',
        'solar_system_luminosity' => 'integer',
        'solar_system_initial_x' => 'integer',
        'solar_system_initial_y' => 'integer',
        'solar_system_initial_z' => 'integer'
    ];

    // Relation avec l'utilisateur qui a créé le système solaire
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'user_id');
    // }

    // Relation avec la galaxie
    public function galaxy()
    {
        return $this->belongsTo(Galaxy::class, 'galaxy_id', 'galaxy_id');
    }

    // ========== RELATIONS LIKES ==========
    
    // Utilisateurs qui ont liké ce système solaire
    // public function likers()
    // {
    //     return $this->belongsToMany(User::class, 'liker_solar_system', 'solar_system_id', 'user_id')
    //                 ->withPivot('liker_solar_system_date')
    //                 ->orderByPivot('liker_solar_system_date', 'desc');
    // }

    // Relation directe avec la table de likes
    public function likes()
    {
        return $this->hasMany(LikerSolarSystem::class, 'solar_system_id', 'solar_system_id');
    }

    // Relation avec les planètes
    public function planets()
    {
        return $this->hasMany(Planet::class, 'solar_system_id', 'solar_system_id');
    }

    // Relation avec les ownership
    public function owner()
    {
        return $this->hasOneThrough(
            User::class,
            'user_solar_system_ownership',
            'solar_system_id', // Foreign key sur ownership table
            'user_id', // Foreign key sur users table
            'solar_system_id', // Local key sur solar_systems table
            'user_id' // Local key sur ownership table
        );
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

    public function getSystemLikesStats()
    {
        return [
            'solar_system_likes' => $this->getLikesCount(),
            'planet_likes' => LikerPlanet::whereHas('planet', function ($q) {
                $q->where('solar_system_id', $this->solar_system_id);
            })->count(),
            'moon_likes' => LikerMoon::whereHas('moon.planet', function ($q) {
                $q->where('solar_system_id', $this->solar_system_id);
            })->count(),
            'total_system_likes' => $this->getTotalSystemLikes(),
            'recent_likers' => $this->getRecentLikers(5)
        ];
    }

    public function getTotalSystemLikes()
    {
        $solarSystemLikes = $this->getLikesCount();
        
        $planetLikes = LikerPlanet::whereHas('planet', function ($q) {
            $q->where('solar_system_id', $this->solar_system_id);
        })->count();
        
        $moonLikes = LikerMoon::whereHas('moon.planet', function ($q) {
            $q->where('solar_system_id', $this->solar_system_id);
        })->count();

        return $solarSystemLikes + $planetLikes + $moonLikes;
    }

    // public function getRecentLikers($limit = 5)
    // {
    //     return $this->likes()
    //                ->with('user')
    //                ->orderBy('liker_solar_system_date', 'desc')
    //                ->limit($limit)
    //                ->get()
    //                ->map(function ($like) {
    //                    return [
    //                        'user' => $like->user,
    //                        'date' => $like->liker_solar_system_date
    //                    ];
    //                });
    // }

    public function getMostLikedPlanets($limit = 3)
    {
        return Planet::where('solar_system_id', $this->solar_system_id)
                    ->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // Obtenir toutes les lunes du système
    public function moons()
    {
        return Moon::whereHas('planet', function($query) {
            $query->where('solar_system_id', $this->solar_system_id);
        });
    }

    // Scope pour les systèmes les plus populaires
    public static function scopePopular($query, $limit = 10)
    {
        return $query->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit);
    }

    public static function scopeWithLikesStats($query)
    {
        return $query->withCount(['likes', 'planets' => function ($q) {
            $q->withCount('likes');
        }]);
    }

}
