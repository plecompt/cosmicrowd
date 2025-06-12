<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moon extends Model
{
    use HasFactory;

    protected $table = 'moon';
    protected $primaryKey = 'moon_id';
    public $timestamps = false;

    protected $fillable = [
        'moon_desc',
        'moon_name',
        'moon_type',
        'moon_gravity',
        'moon_surface_temp',
        'moon_orbital_longitude',
        'moon_eccentricity',
        'moon_apogee',
        'moon_perigee',
        'moon_orbital_inclination',
        'moon_average_distance',
        'moon_orbital_period',
        'moon_inclination_angle',
        'moon_rotation_period',
        'moon_mass',
        'moon_diameter',
        'moon_rings',
        'moon_initial_x',
        'moon_initial_y',
        'moon_initial_z',
        'planet_id',
        'user_id'
    ];

    protected $casts = [
        'moon_gravity' => 'float',
        'moon_surface_temp' => 'float',
        'moon_orbital_longitude' => 'float',
        'moon_eccentricity' => 'float',
        'moon_apogee' => 'integer',
        'moon_perigee' => 'integer',
        'moon_orbital_inclination' => 'integer',
        'moon_average_distance' => 'integer',
        'moon_orbital_period' => 'integer',
        'moon_inclination_angle' => 'integer',
        'moon_rotation_period' => 'integer',
        'moon_mass' => 'integer',
        'moon_diameter' => 'integer',
        'moon_rings' => 'integer',
        'moon_initial_x' => 'integer',
        'moon_initial_y' => 'integer',
        'moon_initial_z' => 'integer'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planet()
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id');
    }

    public function solarSystem()
    {
        return $this->planet->solarSystem();
    }

    public function galaxy()
    {
        return $this->planet->galaxy();
    }

    // Relations de likes
    public function likes()
    {
        return $this->hasMany(LikerMoon::class, 'moon_id', 'moon_id');
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'liker_moon', 'moon_id', 'user_id')
                    ->withPivot('liker_moon_date')
                    ->orderByPivot('liker_moon_date', 'desc');
    }

    // Méthodes utiles
    public function getLikesCount()
    {
        return $this->likes()->count();
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function getPlanetName()
    {
        return $this->planet->planet_name;
    }

    public function getSystemName()
    {
        return $this->planet->getSystemName();
    }

    public function getGalaxyName()
    {
        return $this->planet->getGalaxyName();
    }

    public function getFullPath()
    {
        return $this->getGalaxyName() . ' > ' . $this->getSystemName() . ' > ' . $this->getPlanetName() . ' > ' . $this->moon_name;
    }

    // ========== MÉTHODES UTILES ==========

    public function getRecentLikers($limit = 5)
    {
        return $this->likes()
                   ->with('user')
                   ->orderBy('liker_moon_date', 'desc')
                   ->limit($limit)
                   ->get()
                   ->map(function ($like) {
                       return [
                           'user' => $like->user,
                           'date' => $like->liker_moon_date
                       ];
                   });
    }

    // Scope pour les lunes les plus populaires
    public static function scopePopular($query, $limit = 10)
    {
        return $query->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit);
    }

    public static function scopeByType($query, $type)
    {
        return $query->where('moon_type', $type);
    }

    public static function scopeWithLikesStats($query)
    {
        return $query->withCount('likes');
    }

    // Lunes spéciales (volcanique, glacée)
    public static function scopeSpecial($query)
    {
        return $query->whereIn('moon_type', ['volcanic', 'icy']);
    }
}
