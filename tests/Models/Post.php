<?php

namespace Oslllo\Larakey\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Test\Models\User;

class Post extends Model
{
    protected $fillable = ['title', 'description'];

    public $timestamps = false;

    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
