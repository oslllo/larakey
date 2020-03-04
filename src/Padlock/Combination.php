<?php

namespace Ghustavh97\Larakey\Padlock;

use Ghustavh97\Larakey\Larakey;
use Illuminate\Support\Collection;
use Ghustavh97\Larakey\Padlock\Config;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Traits\LarakeyHelpers;
use Ghustavh97\Larakey\Exceptions\InvalidArguments;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

class Combination
{
    use LarakeyHelpers;

    protected $id;

    protected $to;

    protected $data;

    protected $guard;

    protected $recursive;

    protected $permissions;
    
    protected $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;

        $this->initialize();
    }

    public function initialize()
    {
        if (\count($this->arguments) > 4) {
            throw InvalidArguments::tooMany();
        }

        collect($this->arguments)->each(function ($argument, $key) {
            if (\is_null($this->permissions) && $key === 0) {
                $permissions = $argument;

                if (\is_string($permissions) && $this->isStringPipe($permissions)) {
                    $permissions = $this->convertPipeToArray($permissions);
                }
        
                if (\is_string($permissions) || \is_object($permissions)) {
                    $permissions = [$permissions];
                }
                
                $this->permissions = $permissions;

                return true;
            }

            if (is_null($this->to) && $this->isTo($argument)) {
                if (\is_string($argument)) {
                    if ($this->isWildcardToken($argument)) {
                        $this->to = $argument;
                    } else {
                        if (! \class_exists($argument) && config(Config::$checkifClassExists)) {
                            throw ClassDoesNotExist::check($argument);
                        }
                        $this->to = new $argument;
                    }
                }
        
                if ($argument instanceof Model) {
                    $this->to = $argument;
                }
            }

            if (\is_null($this->id) && $this->isId($argument)) {
                $this->id = $argument;
            }

            if (\is_null($this->guard) && $this->isGuard($argument)) {
                $this->guard = $this->getGuard($argument);
            }

            if (\is_null($this->recursive) && \is_bool($argument)) {
                $this->recursive = $argument;
            }
        });

        if (! \is_null($this->id) && $this->to instanceof Model && ! $this->to->exists) {
            $this->to = \get_class($this->to)::find($this->id);
        }

        if (\is_null($this->recursive)) {
            $this->recursive = config(Config::$recursionOnPermissionRevoke);
        }

        $this->set();
    }

    protected function set()
    {
        $this->data = collect([
            'permissions' => $this->permissions,
            'to' => $this->to,
            'guard' => $this->guard,
            'recursive' => $this->recursive,
            'id' => $this->id
        ]);
    }

    public function get(array $only = []): array
    {
        if (\count($only)) {
            return $this->data->only($only)->all();
        }

        return $this->data->all();
    }

    private function isGuard($argument): bool
    {
        return \is_string($argument)
            && ! $this->isWildcardToken($argument)
            && ! \is_bool($argument)
            && ! $this->isClassString($argument)
            && \in_array($argument, \array_keys(config(Config::$authGuards)));
    }

    private function isId($argument): bool
    {
        return (\is_string($argument) || \is_int($argument))
            && ! \is_bool($argument)
            && ! $this->isClassString($argument)
            && ! $this->isWildcardToken($argument)
            && ! \in_array($argument, \array_keys(config(Config::$authGuards)));
    }

    private function isTo($argument): bool
    {
        return ((\is_string($argument) && $this->isClassString($argument))
            || $argument instanceof Model
            || $this->isWildcardToken($argument));
    }
}
