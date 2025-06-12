<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_login',
        'user_password',
        'user_email',
        'user_active',
        'user_role',
        'user_last_login',
        'user_date_inscription',
    ];

    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    protected $casts = [
        'user_active' => 'boolean',
        'user_last_login' => 'datetime',
        'user_date_inscription' => 'datetime',
        'password_verified_at' => 'datetime',
    ];

    // Override des méthodes d'authentification Laravel
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    // Relation avec les étoiles créées par l'utilisateur
    public function stars()
    {
        return $this->hasMany(Star::class, 'user_id', 'user_id');
    }

   // ========== RELATIONS LIKES ==========
    
    // Systèmes stellaires likés
    public function likedStars()
    {
        return $this->belongsToMany(Star::class, 'liker_star', 'user_id', 'star_id')
                    ->withPivot('liker_star_date')
                    ->orderByPivot('liker_star_date', 'desc');
    }

    // Planètes likées
    public function likedPlanets()
    {
        return $this->belongsToMany(Planet::class, 'liker_planet', 'user_id', 'planet_id')
                    ->withPivot('liker_planet_date')
                    ->orderByPivot('liker_planet_date', 'desc');
    }

    // Lunes likées
    public function likedMoons()
    {
        return $this->belongsToMany(Moon::class, 'liker_moon', 'user_id', 'moon_id')
                    ->withPivot('liker_moon_date')
                    ->orderByPivot('liker_moon_date', 'desc');
    }

    // Relations directes avec les tables de likes
    public function starLikes()
    {
        return $this->hasMany(LikerStar::class, 'user_id', 'user_id');
    }

    public function planetLikes()
    {
        return $this->hasMany(LikerPlanet::class, 'user_id', 'user_id');
    }

    public function moonLikes()
    {
        return $this->hasMany(LikerMoon::class, 'user_id', 'user_id');
    }

    // ========== MÉTHODES UTILES ==========

    public function hasLikedStar($starId)
    {
        return $this->starLikes()->where('star_id', $starId)->exists();
    }

    public function hasLikedPlanet($planetId)
    {
        return $this->planetLikes()->where('planet_id', $planetId)->exists();
    }

    public function hasLikedMoon($moonId)
    {
        return $this->moonLikes()->where('moon_id', $moonId)->exists();
    }

    public function toggleStarLike($starId)
    {
        return LikerStar::toggleLike($starId, $this->user_id);
    }

    public function togglePlanetLike($planetId)
    {
        return LikerPlanet::toggleLike($planetId, $this->user_id);
    }

    public function toggleMoonLike($moonId)
    {
        return LikerMoon::toggleLike($moonId, $this->user_id);
    }

    public function getLikeStats()
    {
        return [
            'stars_liked' => $this->starLikes()->count(),
            'planets_liked' => $this->planetLikes()->count(),
            'moons_liked' => $this->moonLikes()->count(),
            'total_likes' => $this->starLikes()->count() + 
                            $this->planetLikes()->count() + 
                            $this->moonLikes()->count(),
            'recent_activity' => $this->getRecentLikes(10)
        ];
    }

    public function getRecentLikes($limit = 5)
    {
        $starLikes = $this->starLikes()->with('star')->get()->map(function ($like) {
            return [
                'type' => 'star',
                'item' => $like->star,
                'date' => $like->liker_star_date,
                'name' => $like->star->star_name
            ];
        });

        $planetLikes = $this->planetLikes()->with('planet.star')->get()->map(function ($like) {
            return [
                'type' => 'planet',
                'item' => $like->planet,
                'date' => $like->liker_planet_date,
                'name' => $like->planet->planet_name,
                'system' => $like->planet->star->star_name
            ];
        });

        $moonLikes = $this->moonLikes()->with('moon.planet.star')->get()->map(function ($like) {
            return [
                'type' => 'moon',
                'item' => $like->moon,
                'date' => $like->liker_moon_date,
                'name' => $like->moon->moon_name,
                'planet' => $like->moon->planet->planet_name,
                'system' => $like->moon->planet->star->star_name
            ];
        });

        return $starLikes->concat($planetLikes)
                        ->concat($moonLikes)
                        ->sortByDesc('date')
                        ->take($limit)
                        ->values();
    }
}
