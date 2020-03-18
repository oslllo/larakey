<?php

namespace Oslllo\Larakey\Traits;

use Oslllo\Larakey\Guard;
use Oslllo\Larakey\Larakey;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Exceptions\ClassDoesNotExist;

trait HasLarakey
{
    use HasRoles;
}
