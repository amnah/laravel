<?php

namespace App\Models;

use App\Classes\BaseModel;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'confirmation'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The related password reset models
     */
    public function password_resets()
    {
        return $this->hasMany(PasswordReset::class);
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
