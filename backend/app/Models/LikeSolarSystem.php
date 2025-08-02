<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'like_solar_system_date',
    ];

    protected $casts = [
        'like_solar_system_date' => 'datetime',
    ];

    /**
     * Customize query for composite primary key during save operations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query): \Illuminate\Database\Eloquent\Builder
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

    /**
     * Get the value of the primary key for save query, handling composite keys.
     *
     * @param  string|null  $keyName
     * @return mixed
     */
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

    /**
     * Relation: LikeSolarSystem belongs to User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relation: LikeSolarSystem belongs to SolarSystem.
     *
     * @return BelongsTo
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'solar_system_id');
    }
}
