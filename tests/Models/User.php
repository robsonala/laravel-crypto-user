<?php
namespace Robsonala\CryptoUser\Test\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Robsonala\CryptoUser\Traits\UserEncrypt;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Authorizable, Authenticatable, UserEncrypt;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['email', 'password'];
    public $timestamps = false;
    protected $table = 'users';
}