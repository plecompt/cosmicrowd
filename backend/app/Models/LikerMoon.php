<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikerMoon extends Model
{
    use HasFactory;

    protected $table = 'liker_moon';
    public $timestamps = false;
    public $incrementing = false; // Pas d'ID auto-incrémenté
    protected $primaryKey = ['moon_id', 'user_id']; // Clé composite

    protected $fillable = [
        'liker_moon_date',
        'user_id',
        'moon_id',
    ];

    protected $casts = [
        'liker_moon_date' => 'datetime',
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

    public function moon()
    {
        return $this->belongsTo(Moon::class, 'moon_id', 'moon_id');
    }

    public function planet()
    {
        return $this->moon->planet();
    }

    public function star()
    {
        return $this->moon->planet->star();
    }

    // Méthodes utiles
    public function getMoonWithSystem()
    {
        return $this->moon()->with(['planet.star']);
    }

    public static function isLikedBy($moonId, $userId)
    {
        return self::where('moon_id', $moonId)
                  ->where('user_id', $userId)
                  ->exists();
    }

    public static function toggleLike($moonId, $userId)
    {
        $like = self::where('moon_id', $moonId)
                   ->where('user_id', $userId)
                   ->first();

        if ($like) {
            $like->delete();
            return false; // Unlike
        } else {
            self::create([
                'moon_id' => $moonId,
                'user_id' => $userId,
                'liker_moon_date' => now()
            ]);
            return true; // Like
        }
    }

    public static function getLikesCount($moonId)
    {
        return self::where('moon_id', $moonId)->count();
    }

    public static function getUserLikedMoons($userId)
    {
        return self::where('user_id', $userId)
                  ->with(['moon.planet.star'])
                  ->orderBy('liker_moon_date', 'desc')
                  ->get();
    }

    public static function getPopularMoons($limit = 10)
    {
        return self::select('moon_id', \DB::raw('COUNT(*) as likes_count'))
                  ->groupBy('moon_id')
                  ->orderBy('likes_count', 'desc')
                  ->limit($limit)
                  ->with(['moon.planet.star'])
                  ->get();
    }

    public static function getRecentLikes($limit = 20)
    {
        return self::with(['user', 'moon.planet.star'])
                  ->orderBy('liker_moon_date', 'desc')
                  ->limit($limit)
                  ->get();
    }

    public static function getMoonsByType($type = null, $limit = 10)
    {
        $query = self::select('moon_id', \DB::raw('COUNT(*) as likes_count'))
                    ->groupBy('moon_id')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit)
                    ->with(['moon.planet.star']);

        if ($type) {
            $query->whereHas('moon', function ($q) use ($type) {
                $q->where('moon_type', $type);
            });
        }

        return $query->get();
    }

    public static function getUserLikeStats($userId)
    {
        return [
            'total_likes' => self::where('user_id', $userId)->count(),
            'by_moon_type' => self::where('user_id', $userId)
                                 ->join('moon', 'liker_moon.moon_id', '=', 'moon.moon_id')
                                 ->select('moon.moon_type', \DB::raw('COUNT(*) as count'))
                                 ->groupBy('moon.moon_type')
                                 ->pluck('count', 'moon.moon_type')
                                 ->toArray(),
            'by_system' => self::where('user_id', $userId)
                              ->join('moon', 'liker_moon.moon_id', '=', 'moon.moon_id')
                              ->join('planet', 'moon.planet_id', '=', 'planet.planet_id')
                              ->join('star', 'planet.star_id', '=', 'star.star_id')
                              ->select('star.star_name', \DB::raw('COUNT(*) as count'))
                              ->groupBy('star.star_name')
                              ->orderBy('count', 'desc')
                              ->limit(5)
                              ->pluck('count', 'star.star_name')
                              ->toArray(),
            'recent_likes' => self::where('user_id', $userId)
                                 ->with(['moon.planet.star'])
                                 ->orderBy('liker_moon_date', 'desc')
                                 ->limit(5)
                                 ->get()
        ];
    }

    public static function getSystemMoonLikes($starId)
    {
        return self::whereHas('moon.planet', function ($q) use ($starId) {
                    $q->where('star_id', $starId);
                })
                ->with(['moon.planet'])
                ->get()
                ->groupBy('moon.planet.planet_name')
                ->map(function ($likes) {
                    return $likes->count();
                });
    }
}
