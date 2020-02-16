<?php

namespace Ghustavh97\Guardian\Traits;

use Ghustavh97\Guardian\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Guardian\Exceptions\ClassDoesNotExist;

trait Guardian
{
    use GuardianRoles;
}
