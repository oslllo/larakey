<?php

namespace Ghustavh97\Larakey\Test\Models;

use Illuminate\Auth\Authenticatable;
use Ghustavh97\Larakey\Traits\HasLarakey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Admin extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasLarakey, Authorizable, Authenticatable;

    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'admins';

    public function scopeEmail(Builder $query, $email)
    {
        return $query->where(['email' => $email]);
    }
}
