<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikerSolarSystem extends Model
{
    use HasFactory;

    protected $table = 'liker_solar_system';
    public $timestamps = false;
    public $incrementing = false; // Pas d'ID auto-incrémenté
    protected $primaryKey = ['solar_system_id', 'user_id']; // Clé composite

    protected $fillable = [
        'liker_solar_system_date',
        'user_id',
        'solar_system_id',
    ];

    protected $casts = [
        'liker_solar_system_date' => 'datetime',
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

    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'solar_system_id');
    }

    // Méthodes utiles
    public function getSystem()
    {
        return $this->solarSystem()->with(['planets.moons']);
    }

    public static function isLikedBy($solarSystemId, $userId)
    {
        return self::where('solar_system_id', $solarSystemId)
                  ->where('user_id', $userId)
                  ->exists();
    }

    public static function toggleLike($solarSystemId, $userId)
    {
        $like = self::where('solar_system_id', $solarSystemId)
                   ->where('user_id', $userId)
                   ->first();

        if ($like) {
            $like->delete();
            return false; // Unlike
        } else {
            self::create([
                'solar_system_id' => $solarSystemId,
                'user_id' => $userId,
                'liker_solar_system_date' => now()
            ]);
            return true; // Like
        }
    }

    public static function getLikesCount($solarSystemId)
    {
        return self::where('solar_system_id', $solarSystemId)->count();
    }

    public static function getUserLikedSystems($userId)
    {
        return self::where('user_id', $userId)
                  ->with(['solarSystem.planets.moons'])
                  ->orderBy('liker_solar_system_date', 'desc')
                  ->get();
    }

    public static function getPopularSystems($limit = 10)
    {
        return self::select('solar_system_id', \DB::raw('COUNT(*) as likes_count'))
                  ->groupBy('solar_system_id')
                  ->orderBy('likes_count', 'desc')
                  ->limit($limit)
                  ->with(['solarSystem.planets.moons'])
                  ->get();
    }

    public static function getRecentLikes($limit = 20)
    {
        return self::with(['user', 'solarSystem'])
                  ->orderBy('liker_solar_system_date', 'desc')
                  ->limit($limit)
                  ->get();
    }
}
