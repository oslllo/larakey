<?php

namespace Ghustavh97\Larakey\Exceptions;

use InvalidArgumentException;

class StrictPermission extends InvalidArgumentException
{
    /**
     * Exception function for when permission scope on assignment is not set.
     *
     * @return self
     */
    public static function assignment(): self
    {
        return new static("Permission scope on assignment is not set.");
    }

    /**
     * Exception function for when permission scope on revoke is not set
     *
     * @return self
     */
    public static function revoke(): self
    {
        return new static("Permission scope on revoke is not set.");
    }
}
