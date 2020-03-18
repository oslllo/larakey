<?php

namespace Oslllo\Larakey\Test;

use Oslllo\Larakey\Test\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;

    protected $guard_name = 'web';
}
