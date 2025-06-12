<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Star extends Model
{
    use HasFactory;

    protected $table = 'star';
    protected $primaryKey = 'star_id';
    public $timestamps = false;

    protected $fillable = [
        'star_desc',
        'star_name',
        'star_type',
        'star_gravity',
        'star_surface_temp',
        'star_diameter',
        'star_mass',
        'star_luminosity',
        'star_initial_x',
        'star_initial_y',
        'star_initial_z',
        'galaxy_id',
        'user_id'
    ];

    protected $casts = [
        'star_gravity' => 'float',
        'star_surface_temp' => 'float',
        'star_diameter' => 'integer',
        'star_mass' => 'integer',
        'star_luminosity' => 'integer',
        'star_initial_x' => 'integer',
        'star_initial_y' => 'integer',
        'star_initial_z' => 'integer'
    ];

    // Relation avec l'utilisateur qui a créé l'étoile
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

   // ========== RELATIONS LIKES ==========
    
    // Utilisateurs qui ont liké cette étoile
    public function likers()
    {
        return $this->belongsToMany(User::class, 'liker_star', 'star_id', 'user_id')
                    ->withPivot('liker_star_date')
                    ->orderByPivot('liker_star_date', 'desc');
    }

    // Relation directe avec la table de likes
    public function likes()
    {
        return $this->hasMany(LikerStar::class, 'star_id', 'star_id');
    }

    // Relation avec les planètes
    public function planets()
    {
        return $this->hasMany(Planet::class, 'star_id', 'star_id');
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
            'star_likes' => $this->getLikesCount(),
            'planet_likes' => LikerPlanet::whereHas('planet', function ($q) {
                $q->where('star_id', $this->star_id);
            })->count(),
            'moon_likes' => LikerMoon::whereHas('moon.planet', function ($q) {
                $q->where('star_id', $this->star_id);
            })->count(),
            'total_system_likes' => $this->getTotalSystemLikes(),
            'recent_likers' => $this->getRecentLikers(5)
        ];
    }

    public function getTotalSystemLikes()
    {
        $starLikes = $this->getLikesCount();
        
        $planetLikes = LikerPlanet::whereHas('planet', function ($q) {
            $q->where('star_id', $this->star_id);
        })->count();
        
        $moonLikes = LikerMoon::whereHas('moon.planet', function ($q) {
            $q->where('star_id', $this->star_id);
        })->count();

        return $starLikes + $planetLikes + $moonLikes;
    }

    public function getRecentLikers($limit = 5)
    {
        return $this->likes()
                   ->with('user')
                   ->orderBy('liker_star_date', 'desc')
                   ->limit($limit)
                   ->get()
                   ->map(function ($like) {
                       return [
                           'user' => $like->user,
                           'date' => $like->liker_star_date
                       ];
                   });
    }

    public function getMostLikedPlanets($limit = 3)
    {
        return Planet::where('star_id', $this->star_id)
                    ->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit)
                    ->get();
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
