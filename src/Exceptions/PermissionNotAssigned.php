<?php

namespace Oslllo\Larakey\Exceptions;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Contracts\Permission;

class PermissionNotAssigned extends InvalidArgumentException
{
    /**
     * Exception function for when a permission with name and guard already exists.
     *
     * @param \Oslllo\Larakey\Contracts\Permission $permission
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
