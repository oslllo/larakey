<?php

namespace Ghustavh97\Guardian\Exceptions;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Guardian\Contracts\Permission;

class PermissionNotAssigned extends InvalidArgumentException
{
    public static function revoke(Permission $permission, Model $model, Array $pivot)
    {
        return new static("A `{$permissionName}` permission already exists for guard `{$guardName}`.");
    }
}
