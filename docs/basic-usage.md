# Basic Usage

First, add the ```Oslllo\Larakey\Traits\HasLarakey``` trait to your ```User``` model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Oslllo\Larakey\Traits\HasLarakey;

class User extends Authenticatable
{
    use HasLarakey;

    // ...
}
```

This package allows for users to be associated with permissions and roles. Every role is associated with multiple permissions. A ```Role``` and a ```Permission``` are regular Eloquent models. They require a name and can be created like this:

```php
use Oslllo\Larakey\Models\Role;
use Oslllo\Larakey\Models\Permission;

$role = Role::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);
```
A permission can be assigned to a role using 1 of these methods:

```php
$role->givePermissionTo($permission);
$permission->assignRole($role);
```
Multiple permissions can be synced to a role using 1 of these methods:

```php
$role->syncPermissions($permissions);
$permission->syncRoles($roles);
```
A permission can be removed from a role using 1 of these methods:

```php
$role->revokePermissionTo($permission);
$permission->removeRole($role);
```

If you’re using multiple guards the ```guard_name``` attribute needs to be set as well. Read about it in the [using multiple guards](#using-multiple-guards) section of the readme.

The ```Larakey``` trait adds Eloquent relationships to your models, which can be accessed directly or used as a base query:

```php
// get a list of all permissions directly assigned to the user
$permissionNames = $user->getPermissionNames(); // collection of name strings
$permissions = $user->permissions; // collection of permission objects

// get all permissions for the user, either directly, or from roles, or from both
$permissions = $user->getDirectPermissions();
$permissions = $user->getPermissionsViaRoles();
$permissions = $user->getAllPermissions();

// get the names of the user's roles
$roles = $user->getRoleNames(); // Returns a collection
```

The ```Larakey``` trait also adds a ```role``` scope to your models to scope the query to certain roles or permissions:

```php
$users = User::role('writer')->get(); // Returns only users with the role 'writer'
```

The ```role``` scope can accept a string, a ```\Oslllo\Larakey\Models\Role``` object or an ```\Illuminate\Support\Collection``` object.

The same trait also adds a scope to only get users that have a certain permission.

```php
$users = User::permission('edit articles')->get(); // Returns only users with the permission 'edit articles' (inherited or directly)
```

The scope can accept a string, a ```\Oslllo\Larakey\Models\Permission``` object or an ```\Illuminate\Support\Collection``` object.

