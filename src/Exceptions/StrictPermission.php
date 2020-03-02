<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class StrictPermission extends InvalidArgumentException
{
    public static function assignment()
    {
        return new static("Permission scope 'e.g to a class or model instance' should be set in strict mode.");
    }
}
