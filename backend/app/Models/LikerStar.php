<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikerStar extends Model
{
    use HasFactory;

    protected $table = 'liker_star';
    public $timestamps = false;
    public $incrementing = false; // Pas d'ID auto-incrémenté
    protected $primaryKey = ['star_id', 'user_id']; // Clé composite

    protected $fillable = [
        'liker_star_date',
        'user_id',
        'star_id',
    ];

    protected $casts = [
        'liker_star_date' => 'datetime',
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

    public function star()
    {
        return $this->belongsTo(Star::class, 'star_id', 'star_id');
    }

    // Méthodes utiles
    public function getSystem()
    {
        return $this->star()->with(['planets.moons']);
    }

    public static function isLikedBy($starId, $userId)
    {
        return self::where('star_id', $starId)
                  ->where('user_id', $userId)
                  ->exists();
    }

    public static function toggleLike($starId, $userId)
    {
        $like = self::where('star_id', $starId)
                   ->where('user_id', $userId)
                   ->first();

        if ($like) {
            $like->delete();
            return false; // Unlike
        } else {
            self::create([
                'star_id' => $starId,
                'user_id' => $userId,
                'liker_star_date' => now()
            ]);
            return true; // Like
        }
    }

    public static function getLikesCount($starId)
    {
        return self::where('star_id', $starId)->count();
    }

    public static function getUserLikedSystems($userId)
    {
        return self::where('user_id', $userId)
                  ->with(['star.planets.moons'])
                  ->orderBy('liker_star_date', 'desc')
                  ->get();
    }

    public static function getPopularSystems($limit = 10)
    {
        return self::select('star_id', \DB::raw('COUNT(*) as likes_count'))
                  ->groupBy('star_id')
                  ->orderBy('likes_count', 'desc')
                  ->limit($limit)
                  ->with(['star.planets.moons'])
                  ->get();
    }

    public static function getRecentLikes($limit = 20)
    {
        return self::with(['user', 'star'])
                  ->orderBy('liker_star_date', 'desc')
                  ->limit($limit)
                  ->get();
    }
}
