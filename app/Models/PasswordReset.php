<?php

namespace App\Models;

use App\Classes\BaseModel;

use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $created_at
 * @property string $consumed_at
 *
 * @property User $user
 */
class PasswordReset extends BaseModel
{
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'token',
    ];

    /**
     * The related user model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get model by token
     * @param string $token
     * @return static
     */
    public static function getByToken($token)
    {
        // check for token that hasn't been consumed and hasn't expired yet
        $expireMinutes = config('auth.passwords.users.expire');
        $afterTime = date("Y-m-d H:i:s", strtotime("-{$expireMinutes} minutes"));
        return static::query()
            ->where('token', $token)
            ->where('consumed_at', null)
            ->where('created_at', '>=', $afterTime)
            ->first();
    }

    /**
     * Update or create token for user
     * @param int $userId
     * @return static
     */
    public static function setTokenForUser($userId)
    {
        // attempt to find existing token or create new
        $passwordReset = static::firstOrNew([
            'user_id' => $userId,
            'consumed_at' => null,
        ]);

        // set token
        $token = Str::random(60);
        $passwordReset->forceFill([
            'token' => $token,
            'created_at' => date("Y-m-d H:i:s"),
        ])->save();

        return $passwordReset;
    }

    /**
     * Consume password reset
     */
    public function consume()
    {
        $this->consumed_at = $this->freshTimestamp();
        $this->save();
    }
}
