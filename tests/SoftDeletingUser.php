<?php

namespace Ghustavh97\Larakey\Test;

use Ghustavh97\Larakey\Test\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;

    protected $guard_name = 'web';
}
