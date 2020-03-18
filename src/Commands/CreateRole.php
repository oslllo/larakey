<?php

namespace Oslllo\Larakey\Commands;

use Illuminate\Console\Command;
use Oslllo\Larakey\Contracts\Role as RoleContract;
use Oslllo\Larakey\Contracts\Permission as PermissionContract;

class CreateRole extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected $signature = 'permission:create-role
        {name : The name of the role}
        {guard? : The name of the guard}
        {permissions? : A list of permissions to assign to the role, separated by | }';

    /**
     * Command signature.
     *
     *  @var string
     */
    protected $description = 'Create a role';

    /**
     * Command handle function
     *
     * @return void
     */
    public function handle()
    {
        $roleClass = app(RoleContract::class);

        $role = $roleClass::findOrCreate($this->argument('name'), $this->argument('guard'));

        $role->givePermissionTo($this->makePermissions($this->argument('permissions')));

        $this->info("Role `{$role->name}` created");
    }

    /**
     * Find or create given permissions in string.
     *
     * @param string|null $string
     *
     * @return \Illuminate\Support\Collection
     */
    protected function makePermissions($string = null)
    {
        if (empty($string)) {
            return;
        }

        $permissionClass = app(PermissionContract::class);

        $permissions = explode('|', $string);

        $models = [];

        foreach ($permissions as $permission) {
            $models[] = $permissionClass::findOrCreate(trim($permission), $this->argument('guard'));
        }

        return collect($models);
    }
}
