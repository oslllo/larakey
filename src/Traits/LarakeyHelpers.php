<?php

namespace Oslllo\Larakey\Traits;

use Oslllo\Larakey\Guard;
use Oslllo\Larakey\Larakey;
use Illuminate\Support\Collection;
use Oslllo\Larakey\Padlock\Key;
use Oslllo\Larakey\Padlock\Combination;

trait LarakeyHelpers
{
    /**
     * Checks if argument is class string.
     *
     * @param mixed $argument
     * @return boolean
     */
    private function isClassString($argument): bool
    {
        return \strpos($argument, '\\') !== false;
    }

    /**
     * Checks if argument is string pipe.
     *
     * @param string $argument
     * @return boolean
     */
    private function isStringPipe(string $argument): bool
    {
        return false !== \strpos($argument, '|');
    }

    /**
     * Checks if argument is wildcard token.
     *
     * @param mixed $argument
     * @return boolean
     */
    private function isWildcardToken($argument): bool
    {
        return $argument === Larakey::WILDCARD_TOKEN;
    }

    /**
     * Converts string pipe to array.
     *
     * @param string $pipeString
     * @return array
     */
    private static function convertPipeToArray(string $pipeString): array
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

    /**
     * Returns guard names a collection.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    /**
     * Returns default guard name.
     *
     * @return string
     */
    private function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    /**
     * Returns guard with name (default guard is returned if null is given).
     *
     * @param string|null $guard
     * @return string
     */
    private function getGuard($guard): string
    {
        return $guard ? $guard : $this->getDefaultGuardName();
    }

    /**
     * Returns the permission key.
     *
     * @param string|object|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \Oslllo\Larakey\Padlock\Key
     */
    private function getPermissionKey($model, $permission = null): Key
    {
        return app()->makeWith(Key::class, ['model' => $model, 'permission' => $permission]);
    }

    /**
     * Returns combination class instance.
     *
     * @param array $arguments
     * @return \Oslllo\Larakey\Padlock\Combination
     */
    private function combination(array $arguments, bool $multiplePermissions = false): Combination
    {
        return app()->makeWith(Combination::class, [
            'arguments' => $arguments,
            'multiplePermissions' => $multiplePermissions
        ]);
    }
}
