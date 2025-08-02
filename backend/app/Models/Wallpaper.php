<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LikeWallpaper;

class Wallpaper extends Model
{
    protected $table = 'wallpaper';
    protected $primaryKey = 'wallpaper_id';
    
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'galaxy_id', 
        'solar_system_id',
        'wallpaper_settings'
    ];

    /**
     * Get the user who created the wallpaper.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the galaxy associated with the wallpaper.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function galaxy(): BelongsTo
    {
        return $this->belongsTo(Galaxy::class, 'galaxy_id', 'galaxy_id');
    }

    /**
     * Get the solar system associated with the wallpaper.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class, 'solar_system_id', 'solar_system_id');
    }

    /**
     * Get the likes for this wallpaper.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(LikeWallpaper::class, 'wallpaper_id', 'wallpaper_id');
    }
}