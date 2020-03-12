<?php

namespace Ghustavh97\Larakey;

use Ghustavh97\Larakey\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Exceptions\InvalidArguments;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;
use Ghustavh97\Larakey\Padlock\Key;
use Ghustavh97\Larakey\Padlock\Config;
use Ghustavh97\Larakey\Contracts\Role;
use Ghustavh97\Larakey\Contracts\Permission;

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
     * @return \Ghustavh97\Larakey\Contracts\Permission
     */
    public function getPermissionClass(): Permission
    {
        return app($this->permissionClass);
    }

    /**
     * Returns an instance of the role class.
     *
     * @return \Ghustavh97\Larakey\Contracts\Role
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
     * @return \Ghustavh97\Larakey\Contracts\HasPermission
     */
    public function getmodelHasPermissionClass(): HasPermission
    {
        return app($this->modelHasPermissionClass);
    }
}
