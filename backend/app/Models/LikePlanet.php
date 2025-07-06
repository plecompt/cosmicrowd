<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikePlanet extends Model
{
    use HasFactory;

    protected $table = 'like_planet';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['planet_id', 'user_id'];

    protected $fillable = [
        'planet_id',
        'user_id',
        'like_planet_date'
    ];

    protected $casts = [
        'like_planet_date' => 'datetime',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function planet()
    {
        return $this->belongsTo(Planet::class, 'planet_id', 'planet_id');
    }
}