<?php

namespace Ghustavh97\Guardian\Exceptions;

use InvalidArgumentException;

class StrictModeRestriction extends InvalidArgumentException
{
    public static function assignment()
    {
        return new static("Permission field to_id and to_type should be set in strict mode.");
    }
}
