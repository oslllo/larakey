<?php

namespace Ghustavh97\Larakey\Padlock;

use Ghustavh97\Larakey\Larakey;
use Illuminate\Support\Collection;
use Ghustavh97\Larakey\Padlock\Config;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Exceptions\InvalidArguments;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

class Combination
{
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
        if (count($this->arguments) > 4) {
            throw InvalidArguments::tooMany();
        }

        collect($this->arguments)->each(function ($argument, $key) {
            if ($this->permissions === null && $key === 0) {
                $permissions = $argument;

                if (is_string($permissions) && false !== strpos($permissions, '|')) {
                    $permissions = app(Larakey::class)->convertPipeToArray($permissions);
                }
        
                if (is_string($permissions) || is_object($permissions)) {
                    $permissions = [$permissions];
                }
                
                $this->permissions = $permissions;

                return true;
            }

            if ($this->to === null
                && ((\is_string($argument) && \strpos($argument, '\\') !== false)
                || $argument instanceof Model)) {
                if (is_string($argument)) {
                    if (! class_exists($argument)) { //!Config
                        throw ClassDoesNotExist::check($argument);
                    }

                    $this->to = new $argument;
                }
        
                if ($argument instanceof Model) {
                    $this->to = $argument;
                }
            }

            if ($this->id === null
                && (is_string($argument) || is_int($argument))
                && ! is_bool($argument)
                && ! \strpos($argument, '\\') !== false
                && ! in_array($argument, array_keys(config(Config::$authGuards)))) {
                    $this->id = $argument;
            }

            if ($this->guard === null && is_string($argument)
                && $argument != Larakey::WILDCARD_TOKEN
                && ! is_bool($argument)
                && ! \strpos($argument, '\\') !== false
                && in_array($argument, array_keys(config(Config::$authGuards)))) {
                    $this->guard = app(Larakey::class)->getGuard($argument);
            }

            if ($this->recursive === null && \is_bool($argument)) {
                $this->recursive = $argument;
            }
        });

        if ($this->to !== null
            && $this->to instanceof Model
            && ! $this->to->exists
            && $this->id !== null) {
                $this->to = get_class($this->to)::find($this->id);
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
        if (count($only)) {
            return $this->data->only($only)->all();
        }

        return $this->data->all();
    }
}
