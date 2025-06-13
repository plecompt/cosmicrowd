<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_login',
        'user_email',
        'user_password',
        'user_active',
        'user_role',
        'user_last_login',
        'user_date_inscription',
        'email_verified_at'
    ];

    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    protected $casts = [
        'user_active' => 'boolean',
        'user_last_login' => 'datetime',
        'user_date_inscription' => 'datetime',
        'email_verified_at' => 'datetime',
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

    // Systèmes solaires possédés
    public function ownedSolarSystems()
    {
        return $this->belongsToMany(SolarSystem::class, 'user_solar_system_ownership', 'user_id', 'solar_system_id')
                    ->withPivot('ownership_type', 'owned_at');
    }

   // ========== RELATIONS LIKES ==========
    
    // Systèmes solaires likés
    public function likedSolarSystems()
    {
        return $this->belongsToMany(SolarSystem::class, 'liker_solar_system', 'user_id', 'solar_system_id')
                    ->withPivot('liker_solar_system_date')
                    ->orderByPivot('liker_solar_system_date', 'desc');
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
    public function solarSystemLikes()
    {
        return $this->hasMany(LikerSolarSystem::class, 'user_id', 'user_id');
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

    public function hasLikedSolarSystem($solarSystemId)
    {
        return $this->solarSystemLikes()->where('solar_system_id', $solarSystemId)->exists();
    }

    public function hasLikedPlanet($planetId)
    {
        return $this->planetLikes()->where('planet_id', $planetId)->exists();
    }

    public function hasLikedMoon($moonId)
    {
        return $this->moonLikes()->where('moon_id', $moonId)->exists();
    }

    public function toggleSolarSystemLike($solarSystemId)
    {
        return LikerSolarSystem::toggleLike($solarSystemId, $this->user_id);
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
            'solar_systems_liked' => $this->solarSystemLikes()->count(),
            'planets_liked' => $this->planetLikes()->count(),
            'moons_liked' => $this->moonLikes()->count(),
            'total_likes' => $this->solarSystemLikes()->count() + 
                            $this->planetLikes()->count() + 
                            $this->moonLikes()->count(),
            'recent_activity' => $this->getRecentLikes(10)
        ];
    }

    public function getRecentLikes($limit = 5)
    {
        $solarSystemLikes = $this->solarSystemLikes()->with('solarSystem')->get()->map(function ($like) {
            return [
                'type' => 'solar_system',
                'item' => $like->solarSystem,
                'date' => $like->liker_solar_system_date,
                'name' => $like->solarSystem->solar_system_name
            ];
        });

        $planetLikes = $this->planetLikes()->with('planet.solarSystem')->get()->map(function ($like) {
            return [
                'type' => 'planet',
                'item' => $like->planet,
                'date' => $like->liker_planet_date,
                'name' => $like->planet->planet_name,
                'system' => $like->planet->solarSystem->solar_system_name
            ];
        });

        $moonLikes = $this->moonLikes()->with('moon.planet.solarSystem')->get()->map(function ($like) {
            return [
                'type' => 'moon',
                'item' => $like->moon,
                'date' => $like->liker_moon_date,
                'name' => $like->moon->moon_name,
                'planet' => $like->moon->planet->planet_name,
                'system' => $like->moon->planet->solarSystem->solar_system_name
            ];
        });

        return $solarSystemLikes->concat($planetLikes)
                        ->concat($moonLikes)
                        ->sortByDesc('date')
                        ->take($limit)
                        ->values();
    }
}
