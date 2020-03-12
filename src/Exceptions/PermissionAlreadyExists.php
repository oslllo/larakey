<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class PermissionAlreadyExists extends InvalidArgumentException
{
    /**
     * Exception function for when a permission with guard already exists.
     *
     * @param string $permissionName
     * @param string $guardName
     *
     * @return self
     */
    public static function create(string $permissionName, string $guardName): self
    {
        return new static("A `{$permissionName}` permission already exists for guard `{$guardName}`.");
    }
}
