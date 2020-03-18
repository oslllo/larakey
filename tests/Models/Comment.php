<?php

namespace Oslllo\Larakey\Test\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['description'];

    public $timestamps = false;

    protected $table = 'comments';
}
