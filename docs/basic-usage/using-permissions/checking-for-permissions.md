# <u>Checking For Permissions</u>
 > The function `hasPermissionTo()` OR `can()` can be used to check if a user has direct permission or permission via role.
#### Description
```php
hasPermissionTo(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```
#### Arguments
- ***$permission***
    - Type : `int` | `string` | `array` | `\Oslllo\Larakey\Contracts\Permission`
    - Description : The permission to give to the user.
- ***$model***
    - Type : `string` | `\Illuminate\Database\Eloquent\Model`
    - Description : The model class or instance to be used with the permission to limit scope.
- ***$modelId***
    - Type : `string` | `int`
    - Description : Used to indicate the id of a model when only a class name string is provided to `$model`. 
    - Note : ***`$model` must be present when this value is used.***
- ***$guard***
    - Type : `string`
    - Description : The guard to be used with `$permission`. 

#### Returns
Returns `boolean`.

---

## Examples

```php
//Check for permissions
$post = Post::find(1);

$user->hasPermissionTo('edit');
$user->hasPermissionTo('edit', '*');
$user->hasPermissionTo('edit', $post);
$user->hasPermissionTo('edit', Post::class);
$user->hasPermissionTo('edit', Post::class, 1);
```

Also see [Assigning Permissions Examples](basic-usage/using-permissions/assigning-permissions.md#examples) OR [Revoking Permissions Examples](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md#examples)

You may pass an integer representing the permission id.

```php
//Check for permissions
$user->hasPermissionTo('1');
$user->hasPermissionTo(Permission::find(1)->id);
$user->hasPermissionTo($somePermission->id);
```

Saved permissions will be registered with the `Illuminate\Auth\Access\Gate` class for the default guard. So you can
check if a user has a permission with Laravel's default `can` function:

```php
//Check for permissions
$user->can('edit', Article::class);
```
