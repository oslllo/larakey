<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class RoleAlreadyExists extends InvalidArgumentException
{
    /**
     * Exception function for when a permission with name and guard already exists.
     *
     * @param string $roleName
     * @param string $guardName
     *
     * @return self
     */
    public static function create(string $roleName, string $guardName): self
    {
        return new static("A role `{$roleName}` already exists for guard `{$guardName}`.");
    }
}
