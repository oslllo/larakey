<?php

namespace Oslllo\Larakey\Exceptions;

use InvalidArgumentException;
use Illuminate\Support\Collection;

class GuardDoesNotMatch extends InvalidArgumentException
{
    /**
     * Exception function for when a guard does not match on permission or role creation.
     *
     * @param string $givenGuard
     * @param \Illuminate\Support\Collection
     *
     * @return self
     */
    public static function create(string $givenGuard, Collection $expectedGuards): self
    {
        return new static("The given role or permission should use guard `{$expectedGuards->implode(', ')}` instead of `{$givenGuard}`.");
    }
}
