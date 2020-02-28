<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class InvalidArguments extends InvalidArgumentException
{
    public static function tooMany()
    {
        return new static("Too many arguments passed");
    }
}
