<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Contracts\Permission;

class PermissionNotAssigned extends InvalidArgumentException
{
    /**
     * Exception function for when a permission with name and guard already exists.
     *
     * @param \Ghustavh97\Larakey\Contracts\Permission $permission
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $pivot
     *
     * @return self
     */
    public static function revoke(Permission $permission, Model $model, array $pivot): self
    {
        return new static("A `{$permissionName}` permission already exists for guard `{$guardName}`.");
    }
}
