<?php

namespace Oslllo\Larakey\Exceptions;

use InvalidArgumentException;

class ClassDoesNotExist extends InvalidArgumentException
{
    /**
     * Exception function for when class with namespace does not exist.
     *
     * @param string $class
     *
     * @return self
     */
    public static function check(string $class): self
    {
        return new static("Class `{$class}` does not exist.");
    }
}
