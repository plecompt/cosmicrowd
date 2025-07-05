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

  // ========== RELATIONS LIKES (VERSION SIMPLIFIÉE) ==========
    
    public function solarSystemLikes()
    {
        return $this->hasMany(LikeSolarSystem::class, 'user_id', 'user_id');
    }

    public function planetLikes()
    {
        return $this->hasMany(LikePlanet::class, 'user_id', 'user_id');
    }

    public function moonLikes()
    {
        return $this->hasMany(LikeMoon::class, 'user_id', 'user_id');
    }

    // ========== MÉTHODES UTILES ==========

    public function hasLikedSolarSystem($solarSystemId): bool
    {
        return $this->solarSystemLikes()->where('solar_system_id', $solarSystemId)->exists();
    }

    public function hasLikedPlanet($planetId): bool
    {
        return $this->planetLikes()->where('planet_id', $planetId)->exists();
    }

    public function hasLikedMoon($moonId): bool
    {
        return $this->moonLikes()->where('moon_id', $moonId)->exists();
    }

    public function toggleSolarSystemLike($solarSystemId)
    {
        return LikeSolarSystem::toggleLike($solarSystemId, $this->user_id);
    }

    public function togglePlanetLike($planetId)
    {
        return LikePlanet::toggleLike($planetId, $this->user_id);
    }

    public function toggleMoonLike($moonId)
    {
        return LikeMoon::toggleLike($moonId, $this->user_id);
    }

    public function getLikeStats(): array
    {
        $solarSystemCount = $this->solarSystemLikes()->count();
        $planetCount = $this->planetLikes()->count();
        $moonCount = $this->moonLikes()->count();

        return [
            'solar_systems_liked' => $solarSystemCount,
            'planets_liked' => $planetCount,
            'moons_liked' => $moonCount,
            'total_likes' => $solarSystemCount + $planetCount + $moonCount,
            'recent_activity' => $this->getRecentLikes(10)
        ];
    }

    public function getRecentLikes($limit = 5): \Illuminate\Support\Collection
    {
        $solarSystemLikes = $this->solarSystemLikes()
            ->with('solarSystem')
            ->get()
            ->map(fn($like) => [
                'type' => 'solar_system',
                'item' => $like->solarSystem,
                'date' => $like->like_solar_system_date,
                'name' => $like->solarSystem->solar_system_name
            ]);

        $planetLikes = $this->planetLikes()
            ->with('planet.solarSystem')
            ->get()
            ->map(fn($like) => [
                'type' => 'planet',
                'item' => $like->planet,
                'date' => $like->like_planet_date,
                'name' => $like->planet->planet_name,
                'system' => $like->planet->solarSystem->solar_system_name
            ]);

        $moonLikes = $this->moonLikes()
            ->with('moon.planet.solarSystem')
            ->get()
            ->map(fn($like) => [
                'type' => 'moon',
                'item' => $like->moon,
                'date' => $like->like_moon_date,
                'name' => $like->moon->moon_name,
                'planet' => $like->moon->planet->planet_name,
                'system' => $like->moon->planet->solarSystem->solar_system_name
            ]);

        return $solarSystemLikes
            ->concat($planetLikes)
            ->concat($moonLikes)
            ->sortByDesc('date')
            ->take($limit)
            ->values();
    }
}