<?php

namespace Oslllo\Larakey\Commands;

use Illuminate\Console\Command;
use Oslllo\Larakey\Contracts\Permission as PermissionContract;

class CreatePermission extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected $signature = 'permission:create-permission 
                {name : The name of the permission} 
                {guard? : The name of the guard}';

    /**
     * Command description.
     *
     * @var string
     */
    protected $description = 'Create a permission';

    /**
     * Command handle function
     *
     * @return void
     */
    public function handle()
    {
        $permissionClass = app(PermissionContract::class);

        $permission = $permissionClass::findOrCreate($this->argument('name'), $this->argument('guard'));

        $this->info("Permission `{$permission->name}` created");
    }
}
