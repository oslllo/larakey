<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\Guard;
use Ghustavh97\Larakey\Larakey;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

trait HasLarakey
{
    use HasRoles;
}
