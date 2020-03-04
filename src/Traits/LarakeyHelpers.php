<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\Guard;
use Ghustavh97\Larakey\Larakey;
use Illuminate\Support\Collection;
use Ghustavh97\Larakey\Padlock\Key;
use Ghustavh97\Larakey\Padlock\Combination;

trait LarakeyHelpers
{
    private function isClassString($argument): bool
    {
        return \strpos($argument, '\\') !== false;
    }

    private function isStringPipe(string $argument): bool
    {
        return false !== \strpos($argument, '|');
    }

    private function isWildcardToken(string $argument): bool
    {
        return $argument === Larakey::WILDCARD_TOKEN;
    }

    private static function convertPipeToArray(string $pipeString)
    {
        $pipeString = \trim($pipeString);

        if (\strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = \substr($pipeString, 0, 1);
        $endCharacter = \substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return \explode('|', $pipeString);
        }

        if (! \in_array($quoteCharacter, ["'", '"'])) {
            return \explode('|', $pipeString);
        }

        return \explode('|', \trim($pipeString, $quoteCharacter));
    }

    private function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    private function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    private function getGuard($guard): String
    {
        return $guard ? $guard : $this->getDefaultGuardName();
    }

    /**
     * Get the permission key.
     *
     * @param string|object|\Illuminate\Database\Eloquent\Model $to
     *
     * @return \Ghustavh97\Larakey\Padlock\Key
     */
    private function getPermissionKey($to, $permission = null): Key
    {
        return app()->makeWith(Key::class, ['to' => $to, 'permission' => $permission]);
    }

    private function combination(array $arguments): Combination
    {
        return app()->makeWith(Combination::class, ['arguments' => $arguments]);
    }
}
