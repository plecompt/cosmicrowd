<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikeWallpaper extends Model
{
    protected $table = 'like_wallpaper';
    
    public $timestamps = false;
    
    protected $fillable = [
        'wallpaper_id',
        'user_id',
        'like_wallpaper_date'
    ];
    
    protected $dates = [
        'like_wallpaper_date'
    ];
    
    public function wallpaper()
    {
        return $this->belongsTo(Wallpaper::class, 'wallpaper_id', 'wallpaper_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}