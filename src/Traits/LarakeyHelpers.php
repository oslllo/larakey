<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Padlock\Access;
use Ghustavh97\Larakey\Padlock\Config;
use Ghustavh97\Larakey\Exceptions\InvalidArguments;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

trait LarakeyHelpers
{
    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }

    protected function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    protected function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    /**
     * Get the permission scope.
     *
     * @param string|object|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \Ghustavh97\Larakey\Padlock\Access
     */
    protected function getPermissionAccess($to): Access
    {
        return app()->makeWith(Access::class, ['to' => $to]);
    }

    protected function getPermissionArguments(string $functionName, array $arguments): Collection
    {
        // $argumentCount = count($arguments);

        // switch ($functionName) {
        //     case Larakey::HAS_DIRECT_PERMISSION_FUNCTION :

        //         break;
        //     case Larakey::GIVE_PERMISSION_TO_FUNCTION :

        //         break;
        // }

        // dd(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']);
        if (count($arguments) > 4) {
            throw InvalidArguments::tooMany();
        }

        $data = collect(['permissions', 'model', 'guard', 'recursive', 'id'])->mapWithKeys(function ($value) {
            return [$value => null];
        });

        collect($arguments)->each(function ($argument, $key) use ($data) {
            if ($data['permissions'] === null && $key === 0) {
                $permissions = $argument;

                if (is_string($permissions) && false !== strpos($permissions, '|')) {
                    $permissions = $this->convertPipeToArray($permissions);
                }
        
                if (is_string($permissions) || is_object($permissions)) {
                    $permissions = [$permissions];
                }
                
                $data['permissions'] = $permissions;

                return true;
            }

            if ($data['model'] === null
                &&  (\is_string($argument) && \strpos($argument, '\\') !== false)
                || $argument instanceof Model) {
                $model = null;
                
                if (is_string($argument)) {
                    if (! class_exists($argument)) {
                        throw ClassDoesNotExist::check($argument);
                    }

                    $model = new $argument;
                }
        
                if ($argument instanceof Model) {
                    $model = $argument;
                }
        
                $data['model'] = $model;
            }

            if ($data['id'] === null
                && (is_string($argument) || is_int($argument))
                && ! is_bool($argument)
                && ! \strpos($argument, '\\') !== false
                && ! in_array($argument, array_keys(config(Config::$authGuards)))) {
                    $data['id'] = $argument;
            }

            if ($data['guard'] === null && is_string($argument)
                && $argument != Larakey::WILDCARD_TOKEN
                && ! is_bool($argument)
                && ! \strpos($argument, '\\') !== false
                && in_array($argument, array_keys(config(Config::$authGuards)))) {
                    $data['guard'] = $this->getGuard($argument);
            }

            if ($data['recursive'] === null && \is_bool($argument)) {
                $data['recursive'] = $argument;
            }
        });

        if ($data['model'] !== null
            && $data['model'] instanceof Model
            && ! $data['model']->exists
            && $data['id'] !== null) {
                $data['model'] = get_class($data['model'])::find($data['id']);
        }

        return $data;
    }

    protected function getGuard($guard): String
    {
        return $guard ? $guard : $this->getDefaultGuardName();
    }
}
