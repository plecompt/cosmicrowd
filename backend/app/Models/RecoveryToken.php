<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'recovery_token_created_at',
    ];

    protected $casts = [
        'recovery_token_expires_at' => 'datetime',
        'recovery_token_created_at' => 'datetime',
        'recovery_token_used' => 'boolean',
        'recovery_token_user_id' => 'integer',
    ];

    /**
     * Relation: RecoveryToken belongs to User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recovery_token_user_id');
    }

    /**
     * Create a new recovery token for a given user ID.
     * 
     * @param int $userId
     * @param int $expiresInMinutes
     * @return self
     */
    public static function createForUser(int $userId, int $expiresInMinutes = 60): self
    {
        return self::create([
            'recovery_token_user_id' => $userId,
            'recovery_token_value' => Str::random(64),
            'recovery_token_expires_at' => now()->addMinutes($expiresInMinutes),
            'recovery_token_used' => false,
            'recovery_token_created_at' => now(),
        ]);
    }

    /**
     * Check if the token is valid (not used and not expired).
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->recovery_token_used && $this->recovery_token_expires_at > now();
    }

    /**
     * Mark the token as used.
     * 
     * @return bool
     */
    public function markAsUsed(): bool
    {
        return $this->update(['recovery_token_used' => true]);
    }

    /**
     * Find a valid recovery token by its value.
     * 
     * @param string $token
     * @return self|null
     */
    public static function findValidToken(string $token): ?self
    {
        return self::where('recovery_token_value', $token)
            ->where('recovery_token_used', false)
            ->where('recovery_token_expires_at', '>', now())
            ->first();
    }
}
