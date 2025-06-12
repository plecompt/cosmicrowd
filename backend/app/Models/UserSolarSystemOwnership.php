<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSolarSystemOwnership extends Model
{
    protected $table = 'user_solar_system_ownership';
    public $timestamps = false;
    protected $primaryKey = ['user_id', 'solar_system_id'];
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'solar_system_id',
        'ownership_type',
        'owned_at'
    ];

    protected $casts = [
        'owned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }
} 