<?php

namespace Oslllo\Larakey;

use Oslllo\Larakey\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Exceptions\InvalidArguments;
use Oslllo\Larakey\Exceptions\ClassDoesNotExist;
use Oslllo\Larakey\Padlock\Key;
use Oslllo\Larakey\Padlock\Config;
use Oslllo\Larakey\Contracts\Role;
use Oslllo\Larakey\Contracts\Permission;

class Larakey
{
    /** @var string */
    const WILDCARD_TOKEN = '*';
    
    /** @var string */
    protected $roleClass;

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $modelHasPermissionClass;

    /**
     * Larakey constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->roleClass = config(Config::$roleClass);
        $this->permissionClass = config(Config::$permissionClass);
        $this->modelHasPermissionClass = config(Config::$modelHasPermissionClass);
    }

    /**
     * Returns an instance of the permission class.
     *
     * @return \Oslllo\Larakey\Contracts\Permission
     */
    public function getPermissionClass(): Permission
    {
        return app($this->permissionClass);
    }

    /**
     * Returns an instance of the role class.
     *
     * @return \Oslllo\Larakey\Contracts\Role
     */
    public function getRoleClass(): Role
    {
        return app($this->roleClass);
    }

    /**
     * Sets Larakey permission class.
     *
     * @param string $permissionClass
     * @return $this
     */
    public function setPermissionClass(string $permissionClass): self
    {
        $this->permissionClass = $permissionClass;

        return $this;
    }

    /**
     * Sets Larakey role class.
     *
     * @param string $roleClass
     * @return $this
     */
    public function setRoleClass(string $roleClass): self
    {
        $this->roleClass = $roleClass;

        return $this;
    }

    /**
     * Returns an instance of the HasPermission class.
     *
     * @return \Oslllo\Larakey\Contracts\HasPermission
     */
    public function getmodelHasPermissionClass(): HasPermission
    {
        return app($this->modelHasPermissionClass);
    }
}
