<?php

namespace Ghustavh97\Larakey;

use Ghustavh97\Larakey\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Exceptions\InvalidArguments;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;
use Ghustavh97\Larakey\Padlock\Key;
use Ghustavh97\Larakey\Padlock\Config;
use Ghustavh97\Larakey\Padlock\Combination;
use Ghustavh97\Larakey\Contracts\Role;
use Ghustavh97\Larakey\Contracts\Permission;

class Larakey
{
    const WILDCARD_TOKEN = '*';
    
    /** @var string */
    protected $roleClass;

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $modelHasPermissionClass;

    protected $user;

    public function __construct()
    {
        $this->roleClass = config(Config::$roleClass);
        $this->permissionClass = config(Config::$permissionClass);
        $this->modelHasPermissionClass = config(Config::$modelHasPermissionClass);
    }

    // /**
    //  * Get the permission key.
    //  *
    //  * @param string|object|\Illuminate\Database\Eloquent\Model $to
    //  *
    //  * @return \Ghustavh97\Larakey\Padlock\Key
    //  */
    // public function getPermissionKey($to, $permission = null): Key
    // {
    //     return app()->makeWith(Key::class, ['to' => $to, 'permission' => $permission]);
    // }

    // public function combination(array $arguments): Combination
    // {
    //     return app()->makeWith(Combination::class, ['arguments' => $arguments]);
    // }

    /**
     * Get an instance of the permission class.
     *
     * @return \Ghustavh97\Larakey\Contracts\Permission
     */
    public function getPermissionClass(): Permission
    {
        return app($this->permissionClass);
    }

    // public function setUser(Model $user)
    // {
    //     $this->user = $user;
    // }

    // public function getGuardNames(): Collection
    // {
    //     return Guard::getNames($this->user);
    // }

    // public function getDefaultGuardName(): string
    // {
    //     return Guard::getDefaultName($this->user);
    // }

    // public function getGuard($guard): String
    // {
    //     return $guard ? $guard : $this->getDefaultGuardName();
    // }

    /**
     * Get an instance of the role class.
     *
     * @return \Ghustavh97\Larakey\Contracts\Role
     */
    public function getRoleClass(): Role
    {
        return app($this->roleClass);
    }

    public function setPermissionClass($permissionClass)
    {
        $this->permissionClass = $permissionClass;

        return $this;
    }

    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;

        return $this;
    }

    /**
     * Get an instance of the HasPermission class.
     *
     * @return \Ghustavh97\Larakey\Contracts\HasPermission
     */
    public function getmodelHasPermissionClass(): HasPermission
    {
        return app($this->modelHasPermissionClass);
    }

    // public static function convertPipeToArray(string $pipeString)
    // {
    //     $pipeString = trim($pipeString);

    //     if (strlen($pipeString) <= 2) {
    //         return $pipeString;
    //     }

    //     $quoteCharacter = substr($pipeString, 0, 1);
    //     $endCharacter = substr($quoteCharacter, -1, 1);

    //     if ($quoteCharacter !== $endCharacter) {
    //         return explode('|', $pipeString);
    //     }

    //     if (! in_array($quoteCharacter, ["'", '"'])) {
    //         return explode('|', $pipeString);
    //     }

    //     return explode('|', trim($pipeString, $quoteCharacter));
    // }
}
