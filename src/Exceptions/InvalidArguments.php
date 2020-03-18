<?php

namespace Oslllo\Larakey\Exceptions;

use InvalidArgumentException;

class InvalidArguments extends InvalidArgumentException
{
    /**
     * Exception function for when too many arguments are passed to function.
     *
     * @return self
     */
    public static function tooMany(): self
    {
        return new static("Too many arguments passed");
    }
}
