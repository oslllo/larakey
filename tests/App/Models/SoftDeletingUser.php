<?php

namespace Oslllo\Larakey\Test\App\Models;

use Oslllo\Larakey\Test\App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;

    protected $guard_name = 'web';
}
