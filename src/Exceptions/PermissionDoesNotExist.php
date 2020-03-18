<?php

namespace Oslllo\Larakey\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    /**
     * Exception function for when a permission with name and guard does not exist.
     *
     * @param string $permissionName
     * @param string $guardName
     *
     * @return self
     */
    public static function create(string $permissionName, string $guardName = ''): self
    {
        return new static("There is no permission named `{$permissionName}` for guard `{$guardName}`.");
    }

    /**
     * Exception function for when a permission with id and guard does not exist.
     *
     * @param int $permissionId
     * @param string $guardName
     *
     * @return self
     */
    public static function withId(int $permissionId, string $guardName = ''): self
    {
        return new static("There is no [permission] with id `{$permissionId}` for guard `{$guardName}`.");
    }
}
