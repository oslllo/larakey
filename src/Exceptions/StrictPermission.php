<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class StrictPermission extends InvalidArgumentException
{
    public static function assignment()
    {
        return new static("Permission scope should be set when strict permission assignment is enabled.");
    }

    public static function revoke()
    {
        return new static("Permission scope should be set when strict permission assignment is enabled.");
    }
}
