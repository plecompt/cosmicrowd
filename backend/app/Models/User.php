<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\SolarSystem;
use App\Models\LikeSolarSystem;
use App\Models\LikePlanet;
use App\Models\LikeMoon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_login',
        'user_email',
        'user_password',
        'user_active',
        'user_role',
        'user_last_login',
        'user_date_inscription'
    ];

    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    protected $casts = [
        'user_active' => 'boolean',
        'user_last_login' => 'datetime',
        'user_date_inscription' => 'datetime'
    ];

    // Override Laravel auth methods, replace user.password for user.user_password
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    // Override Laravel auth methods, replace user.email for user.user_email
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    // Relations
    public function ownedSolarSystems()
    {
        return $this->hasMany(SolarSystem::class, 'user_id', 'user_id');
    }

    public function solarSystemLikes()
    {
        return $this->hasMany(LikeSolarSystem::class, 'user_id', 'user_id');
    }

    public function planetLikes()
    {
        return $this->hasMany(LikePlanet::class, 'user_id', 'user_id');
    }

    public function moonLikes()
    {
        return $this->hasMany(LikeMoon::class, 'user_id', 'user_id');
    }
}
