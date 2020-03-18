<?php

namespace Oslllo\Larakey\Padlock;

use Oslllo\Larakey\Larakey;
use Illuminate\Support\Collection;
use Oslllo\Larakey\Padlock\Config;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Traits\LarakeyHelpers;
use Oslllo\Larakey\Exceptions\InvalidArguments;
use Oslllo\Larakey\Exceptions\ClassDoesNotExist;

class Combination
{
    use LarakeyHelpers;

    /**
     * The id to a model.
     *
     * @var int|string
     */
    protected $modelId;

    /**
     * Model class or instance for the permission.
     *
     * @var string|\Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Stores class data.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $data;

    /**
     * Permission guard.
     *
     * @var null|string
     */
    protected $guard;

    /**
     * Determines whether or not to revoke a permission recursively.
     *
     * @var null|bool
     */
    protected $recursive;

    /**
     * Permissions.
     *
     * @var string|array|\Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Models\Permission
     */
    protected $permissions;
    
    /**
     * Arguments passed.
     *
     * @var array
     */
    protected $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;

        $this->initialize();
    }

    /**
     * Initialize Combination class.
     *
     * @return void
     */
    public function initialize()
    {
        if (count($this->arguments) > 5) {
            throw InvalidArguments::tooMany();
        }

        collect($this->arguments)->each(function ($argument, $key) {
            if (is_null($this->permissions) && $key === 0) {
                $this->setPermissions($argument);
                return true;
            }

            if ($this->isModelClassOrInstance($argument)) {
                $this->setModel($argument);
            }

            if ($this->isModelId($argument)) {
                $this->setModelId($argument);
            }

            if ($this->isGuard($argument)) {
                $this->setGuard($argument);
            }

            if ($this->isRecursionBoolean($argument)) {
                $this->setRecursive($argument);
            }
        });

        if ($this->modelIdIsSetAndModelDoesNotExist()) {
            $this->setModel(get_class($this->model)::find($this->modelId));
        }

        if (is_null($this->recursive)) {
            $this->setRecursive(config(Config::$recursionOnPermissionRevoke));
        }

        $this->set();
    }

    /**
     * Set Combination class data.
     *
     * @return void
     */
    protected function set()
    {
        $this->data = collect(get_object_vars($this))
                        ->only(['permissions', 'model', 'guard', 'recursive', 'modelId']);
    }

    /**
     * Get class data as an array.
     *
     * @param array $only
     * @return array
     */
    public function get(array $only = []): array
    {
        if (count($only)) {
            return $this->data->only($only)->all();
        }

        return $this->data->all();
    }

    /**
     * Set calss $model variable.
     *
     * @param string|\Illuminate\Database\Eloquent\Model $argument
     * @return void
     */
    private function setModel($argument): void
    {
        switch (true) {
            case $argument instanceof Model:
                $this->model = $argument;
                break;
            case is_string($argument):
                if ($this->isWildcardToken($argument)) {
                    $this->model = $argument;
                } else {
                    if (! class_exists($argument)) {
                        throw ClassDoesNotExist::check($argument);
                    }
                    $this->model = new $argument;
                }
                break;
        }
    }

    /**
     * Set class $modelId variable.
     *
     * @param int|string $modelId
     * @return void
     */
    private function setModelId($modelId): void
    {
        $this->modelId = $modelId;
    }

    /**
     * Set class $guard.
     *
     * @param string $guard
     * @return void
     */
    private function setGuard(string $guard): void
    {
        $this->guard = $this->getGuard($guard);
    }

    /**
     * Set class $recursive variable.
     *
     * @param bool $recursive
     * @return void
     */
    private function setRecursive(bool $recursive): void
    {
        $this->recursive = $recursive;
    }

    /**
     * Set class $permisions variable.
     *
     * @param string|array|\Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Models\Permission $permissions
     * @return void
     */
    private function setPermissions($permissions): void
    {
        if (is_string($permissions) && $this->isStringPipe($permissions)) {
            $permissions = $this->convertPipeToArray($permissions);
        }

        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        if ((is_string($permissions) || is_object($permissions)) && (! $permissions instanceof Collection)) {
            $permissions = [$permissions];
        }
        
        $this->permissions = $permissions;
    }

    /**
     * Determine is $argument is a guard.
     *
     * @param mixed $argument
     * @return boolean
     */
    private function isGuard($argument): bool
    {
        return is_null($this->guard)
            && is_string($argument)
            && ! $this->isWildcardToken($argument)
            && ! is_bool($argument)
            && ! $this->isClassString($argument)
            && in_array($argument, \array_keys(config(Config::$authGuards)));
    }

    /**
     * Determine is $argument is a modelId.
     *
     * @param mixed $argument
     * @return boolean
     */
    private function isModelId($argument): bool
    {
        return is_null($this->modelId)
            && (is_string($argument) || is_int($argument))
            && ! is_bool($argument)
            && ! $this->isClassString($argument)
            && ! $this->isWildcardToken($argument)
            && ! in_array($argument, array_keys(config(Config::$authGuards)));
    }

    /**
     * Determine is $argument is a recursion boolean.
     *
     * @param mixed $argument
     * @return boolean
     */
    private function isRecursionBoolean($argument): bool
    {
        return is_null($this->recursive) && is_bool($argument);
    }

    /**
     * Determine if class $modelId is set and class $model does not exist.
     *
     * @return boolean
     */
    private function modelIdIsSetAndModelDoesNotExist(): bool
    {
        return ! is_null($this->modelId)
            && $this->model instanceof Model
            && ! $this->model->exists;
    }

    /**
     * Determine is $argument is model class string or model instance.
     *
     * @param mixed $argument
     * @return boolean
     */
    private function isModelClassOrInstance($argument): bool
    {
        return is_null($this->model)
            && ((is_string($argument) && $this->isClassString($argument))
            || $argument instanceof Model
            || $this->isWildcardToken($argument));
    }
}
