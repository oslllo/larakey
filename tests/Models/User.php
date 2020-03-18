<?php

namespace Oslllo\Larakey\Test\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Traits\HasLarakey;
use Oslllo\Larakey\Test\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Oslllo\Larakey\Test\Models\Comment;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use HasLarakey, Authorizable, Authenticatable;

    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'users';

    public function scopeEmail(Builder $query, $email)
    {
        return $query->where(['email' => $email]);
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }
}
