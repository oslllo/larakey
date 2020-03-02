<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class ClassDoesNotExist extends InvalidArgumentException
{
    public static function check(string $class)
    {
        return new static("Class `{$class}` does not exist.");
    }
}
