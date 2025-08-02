<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LikeWallpaper extends Model
{
    protected $table = 'like_wallpaper';
    protected $primaryKey = 'wallpaper_id';
    public $timestamps = false;

    protected $fillable = [
        'wallpaper_id',
        'user_id',
        'like_wallpaper_date',
    ];

    protected $dates = [
        'like_wallpaper_date',
    ];

    /**
     * Relation: LikeWallpaper belongs to Wallpaper.
     *
     * @return BelongsTo
     */
    public function wallpaper(): BelongsTo
    {
        return $this->belongsTo(Wallpaper::class, 'wallpaper_id', 'wallpaper_id');
    }

    /**
     * Relation: LikeWallpaper belongs to User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
