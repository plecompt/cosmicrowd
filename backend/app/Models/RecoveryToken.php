<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecoveryToken extends Model
{
    use HasFactory;

    protected $table = 'recovery_token';
    protected $primaryKey = 'recovery_token_id';
    public $timestamps = false;

    protected $fillable = [
        'recovery_token_user_id',
        'recovery_token_value',
        'recovery_token_expires_at',
        'recovery_token_used',
        'recovery_token_created_at'
    ];

    protected $casts = [
        'recovery_token_expires_at' => 'datetime',
        'recovery_token_created_at' => 'datetime',
        'recovery_token_used' => 'boolean',
        'recovery_token_user_id' => 'integer'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'recovery_token_user_id');
    }

    // Create recoveryToken for given userId
    public static function createForUser($userId, $expiresInMinutes = 60)
    {
        return self::create([
            'recovery_token_user_id' => $userId,
            'recovery_token_value' => Str::random(64),
            'recovery_token_expires_at' => now()->addMinutes($expiresInMinutes),
            'recovery_token_used' => false,
            'recovery_token_created_at' => now()
        ]);
    }

    public function isValid()
    {
        return !$this->recovery_token_used && 
               $this->recovery_token_expires_at > now();
    }

    public function markAsUsed()
    {
        $this->update(['recovery_token_used' => true]);
    }

    public static function findValidToken($token)
    {
        return self::where('recovery_token_value', $token)
                   ->where('recovery_token_used', false)
                   ->where('recovery_token_expires_at', '>', now())
                   ->first();
    }
}
