<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeSolarSystem extends Model
{
    use HasFactory;

    protected $table = 'like_solar_system';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['solar_system_id', 'user_id'];

    protected $fillable = [
        'solar_system_id',
        'user_id',
        'like_solar_system_date'
    ];

    protected $casts = [
        'like_solar_system_date' => 'datetime',
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

    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class, 'planet_id', 'planet_id');
    }
}