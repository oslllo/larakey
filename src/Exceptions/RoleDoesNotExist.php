<?php

namespace Oslllo\Larakey\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    /**
     * Exception function for when a role with name does not exist.
     *
     * @param string $roleName
     *
     * @return self
     */
    public static function named(string $roleName): self
    {
        return new static("There is no role named `{$roleName}`.");
    }

    /**
     * Exception function for when a role with id does not exist.
     *
     * @param integer $roleId
     *
     * @return self
     */
    public static function withId(int $roleId): self
    {
        return new static("There is no role with id `{$roleId}`.");
    }
}
