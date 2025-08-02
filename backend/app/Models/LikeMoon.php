<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LikeMoon extends Model
{
    use HasFactory;

    protected $table = 'like_moon';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['moon_id', 'user_id'];

    protected $fillable = [
        'moon_id',
        'user_id',
        'like_moon_date',
    ];

    protected $casts = [
        'like_moon_date' => 'datetime',
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
     * Relation: LikeMoon belongs to User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relation: LikeMoon belongs to Moon.
     *
     * @return BelongsTo
     */
    public function moon(): BelongsTo
    {
        return $this->belongsTo(Moon::class, 'moon_id', 'moon_id');
    }
}
