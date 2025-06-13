<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikerPlanet extends Model
{
    use HasFactory;

    protected $table = 'liker_planet';
    public $timestamps = false;
    public $incrementing = false; // Pas d'ID auto-incrémenté
    protected $primaryKey = ['planet_id', 'user_id']; // Clé composite

    protected $fillable = [
        'planet_id',
        'user_id',
        'liker_planet_date'
    ];

    protected $casts = [
        'liker_planet_date' => 'datetime',
    ];

    // Override pour clé composite
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planet()
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id');
    }

    public function star()
    {
        return $this->planet->star();
    }

    // Méthodes utiles
    public function getPlanetWithSystem()
    {
        return $this->planet()->with(['star', 'moons']);
    }

    public static function isLikedBy($planetId, $userId)
    {
        return self::where('planet_id', $planetId)
                  ->where('user_id', $userId)
                  ->exists();
    }

    public static function toggleLike($planetId, $userId)
    {
        $like = self::where('planet_id', $planetId)
                   ->where('user_id', $userId)
                   ->first();

        if ($like) {
            $like->delete();
            return false; // Unlike
        } else {
            self::create([
                'planet_id' => $planetId,
                'user_id' => $userId,
                'liker_planet_date' => now()
            ]);
            return true; // Like
        }
    }

    public static function getLikesCount($planetId)
    {
        return self::where('planet_id', $planetId)->count();
    }

    public static function getUserLikedPlanets($userId)
    {
        return self::where('user_id', $userId)
                  ->with(['planet.star', 'planet.moons'])
                  ->orderBy('liker_planet_date', 'desc')
                  ->get();
    }

    public static function getPopularPlanets($limit = 10)
    {
        return self::select('planet_id', \DB::raw('COUNT(*) as likes_count'))
                  ->groupBy('planet_id')
                  ->orderBy('likes_count', 'desc')
                  ->limit($limit)
                  ->with(['planet.star', 'planet.moons'])
                  ->get();
    }

    public static function getRecentLikes($limit = 20)
    {
        return self::with(['user', 'planet.star'])
                  ->orderBy('liker_planet_date', 'desc')
                  ->limit($limit)
                  ->get();
    }

    public static function getPlanetsByType($type = null, $limit = 10)
    {
        $query = self::select('planet_id', \DB::raw('COUNT(*) as likes_count'))
                    ->groupBy('planet_id')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit)
                    ->with(['planet.star']);

        if ($type) {
            $query->whereHas('planet', function ($q) use ($type) {
                $q->where('planet_type', $type);
            });
        }

        return $query->get();
    }

    public static function getUserLikeStats($userId)
    {
        return [
            'total_likes' => self::where('user_id', $userId)->count(),
            'by_planet_type' => self::where('user_id', $userId)
                                   ->join('planet', 'liker_planet.planet_id', '=', 'planet.planet_id')
                                   ->select('planet.planet_type', \DB::raw('COUNT(*) as count'))
                                   ->groupBy('planet.planet_type')
                                   ->pluck('count', 'planet.planet_type')
                                   ->toArray(),
            'recent_likes' => self::where('user_id', $userId)
                                 ->with(['planet.star'])
                                 ->orderBy('liker_planet_date', 'desc')
                                 ->limit(5)
                                 ->get()
        ];
    }
}
