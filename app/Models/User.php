<?php

namespace App\Models;

use App\Classes\BaseModel;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string|null $confirmation
 * @property string|null $remember_token
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PasswordReset[] $password_resets
 */
class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable, Notifiable;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'username', 'email', 'confirmation'
    ];

    /**
     * @inheritdoc
     */
    protected $hidden = [
        'password', 'confirmation', 'remember_token',
    ];

    /**
     * The related password reset models
     */
    public function password_resets()
    {
        return $this->hasMany(PasswordReset::class);
    }

    /**
     * Get password rules for validation
     * @return string
     */
    public static function passwordRules()
    {
        return 'required|string|min:3|confirmed';
    }

    /**
     * Set password (hashed)
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $hashed = Hash::make($password);
        return $this->setAttribute('password', $hashed);
    }

    /**
     * Check if user has confirmed his email address
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->getAttribute('confirmation') === null;
    }

    /**
     * Confirm email address
     * return $this
     */
    public function confirmEmail()
    {
        $this->setAttribute('confirmation', null)->save();
        return $this;
    }
}
