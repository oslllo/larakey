# <u>Database Seeding</u>

You may discover that it is best to flush this package's cache before seeding, to avoid cache conflict errors. This can be done directly in a seeder class. Here is a sample seeder, which first clears the cache, creates permissions and then assigns permissions to roles (the order of these steps is intentional):

```php
use Illuminate\Database\Seeder;
use Oslllo\Larakey\Models\Role;
use Oslllo\Larakey\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Oslllo\Larakey\Padlock\Cache::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'writer']);
        $role->givePermissionTo('edit articles');

        // or may be done by chaining
        $role = Role::create(['name' => 'moderator'])
            ->givePermissionTo(['publish articles', 'unpublish articles']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
```

---
