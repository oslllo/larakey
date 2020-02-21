<?php

namespace Ghustavh97\Guardian\Traits;

use Ghustavh97\Guardian\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Guardian\Exceptions\ClassDoesNotExist;

trait GuardianHelpers
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

    protected function getPivot($model): array
    {
        if ($model instanceof Model && $model->exists) {
            $toId = $model->id;
            $toType = \get_class($model);
        } elseif ($model instanceof Model && ! $model->exists) {
            $toId = '*';
            $toType = \get_class($model);
        } elseif (is_string($model)) {
            if (! class_exists($model)) {
                throw ClassDoesNotExist::check($model);
            }
            $toId = '*';
            $toType = $model;
        } else {
            $toId = '*';
            $toType = '*';
        }
        return ['to_id' => $toId, 'to_type' => $toType];
    }

    protected function getArguments(array $arguments): array
    {
        if (count($arguments) > 4) {
            // TODO: Throw too many arguments exception.
            throw new \Exception('Too many arguments');
        }

        $data = [
            'permissions' => null,
            'model' => null,
            'guard' => null,
            'recursive' => null
        ];

        foreach ($arguments as $key => $value) {
            if ($data['permissions'] === null && $key === 0) {
                $permissions = $value;

                if (is_string($permissions) && false !== strpos($permissions, '|')) {
                    $permissions = $this->convertPipeToArray($permissions);
                }
        
                if (is_string($permissions) || is_object($permissions)) {
                    $permissions = [$permissions];
                }
                
                $data['permissions'] = $permissions;

                continue;
            }

            if ($data['model'] === null
                &&  (\is_string($value) && \strpos($value, '\\') !== false)
                || $value instanceof Model) {
                $data['model'] = $this->getPermissionModel($value);
            }

            if ($data['guard'] === null && is_string($value)
                && $value != '*'
                && !is_bool($value)
                && !\strpos($value, '\\') !== false) {
                $data['guard'] = $value;
            }

            if ($data['recursive'] === null && \is_bool($value)) {
                $data['recursive'] = $value;
            }
        }

        if ($data['recursive'] === null) {
            $data['recursive'] = config('guardian.revoke_recursion');
        }

        return $data;
    }

    protected function getGuard($guard): String
    {
        return $guard ? $guard : $this->getDefaultGuardName();
    }
}
