<?php

namespace Ghustavh97\Guardian\Test;

use Ghustavh97\Guardian\Test\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;

    protected $guard_name = 'web';
}
