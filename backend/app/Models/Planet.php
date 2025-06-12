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
        'star_id',
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

    // ========== RELATIONS PRINCIPALES (MANQUANTES) ==========
    
    // Relation avec l'utilisateur créateur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relation avec l'étoile parent (CELLE QUI MANQUAIT)
    public function star()
    {
        return $this->belongsTo(Star::class, 'star_id', 'star_id');
    }

    // Relation avec les lunes
    public function moons()
    {
        return $this->hasMany(Moon::class, 'planet_id', 'planet_id');
    }

    // ========== RELATIONS LIKES ==========
    
    // Utilisateurs qui ont liké cette planète
    public function likers()
    {
        return $this->belongsToMany(User::class, 'liker_planet', 'planet_id', 'user_id')
                    ->withPivot('liker_planet_date')
                    ->orderByPivot('liker_planet_date', 'desc');
    }

    // Relation directe avec la table de likes
    public function likes()
    {
        return $this->hasMany(LikerPlanet::class, 'planet_id', 'planet_id');
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

    public function getPlanetLikesStats()
    {
        return [
            'planet_likes' => $this->getLikesCount(),
            'moon_likes' => $this->moons()->withCount('likes')->get()->sum('likes_count'),
            'total_likes' => $this->getTotalLikes(),
            'recent_likers' => $this->getRecentLikers(5),
            'most_liked_moons' => $this->getMostLikedMoons(3)
        ];
    }

    public function getTotalLikes()
    {
        $planetLikes = $this->getLikesCount();
        $moonLikes = LikerMoon::whereHas('moon', function ($q) {
            $q->where('planet_id', $this->planet_id);
        })->count();

        return $planetLikes + $moonLikes;
    }

    public function getRecentLikers($limit = 5)
    {
        return $this->likes()
                   ->with('user')
                   ->orderBy('liker_planet_date', 'desc')
                   ->limit($limit)
                   ->get()
                   ->map(function ($like) {
                       return [
                           'user' => $like->user,
                           'date' => $like->liker_planet_date
                       ];
                   });
    }

    public function getMostLikedMoons($limit = 3)
    {
        return $this->moons()
                   ->withCount('likes')
                   ->orderBy('likes_count', 'desc')
                   ->limit($limit)
                   ->get();
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

    public static function scopeInSystem($query, $starId)
    {
        return $query->where('star_id', $starId);
    }
}
