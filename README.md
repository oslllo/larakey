# 🔐 Permission and Role handling for Laravel 5.8 and up, based and inspired on [laravel-permission](https://github.com/spatie/laravel-permission) by [Spatie](https://github.com/spatie)

![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/ghustavh97/larakey?include_prereleases)
![Travis (.org) branch](https://img.shields.io/travis/ghustavh97/larakey/master?label=Travis%20CI)
![GitHub](https://img.shields.io/github/license/Ghustavh97/larakey)
![GitHub issues](https://img.shields.io/github/issues/Ghustavh97/larakey)
![GitHub closed issues](https://img.shields.io/github/issues-closed/Ghustavh97/larakey)

ℹ This package will allow you to manage user permissions and roles in a database. It will also allow you to assign a class or model instance to a permission.

# Table of contents

- [Introduction](#introduction)

- [Prerequisites](#prerequisites)

- [Installation](#installation)
    - [Default config file contents](#default-config-file-contents)

- [Basic Usage](#basic-usage)
    - [Eloquent](#eloquent)

    - [Using Permissions](#using-permissions)
        - [***NOTE*** about using permission names in policies](#note-about-using-permission-names-in-policies)
        - [Assigning Permissions](#assigning-permissions)
        - [Revoking Permissions](#revoking-permissions)
            - [Revoking Permissions With Recursion](#revoking-permissions-with-recursion)
        - [Checking For Permissions](#checking-for-permissions)
        - [Checking For Direct Permissions](#checking-for-direct-permissions)
        - [Checking For Any Direct Permission](#checking-for-any-direct-permission)
        - [Get Direct Permission](#get-direct-permissions)
        - [Get Permissions Via Roles](#get-permissions-via-roles)
        - [Get All Permissions](#get-all-permissions)

    - [Using Roles](#using-roles)
        - [Assigning Roles](#assigning-roles)
        - [Revoking Roles](#revoking-roles)
        - [Syncing Roles](#syncing-roles)
        - [Checking For Roles](#checking-for-roles)
        - [Checking For Any Role](#checking-for-any-role)
        - [Checking For All Roles](#checking-for-all-roles)
        - [Using permissions via roles](#using-permissions-via-roles)

    - [Using permissions via roles](#using-permissions-via-roles)

    - [Blade directives](#blade-directives)
        - [Permissions](#permissions)
        - [Roles](#roles)
        - [Blade and Roles](#blade-and-roles)

    - [Defining a Super-Admin](#defining-a-super-admin)
        - [Gate::before](#gate::before)
        - [Gate::after](#gate::after)

    - [Using multiple guards](#using-multiple-guards)
        - [The Downside To Multiple Guards](#the-downside-to-multiple-guards)
        - [Using permissions and roles with multiple guards](#using-permissions-and-roles-with-multiple-guards)
        - [Assigning permissions and roles to guard users](#assigning-permissions-and-roles-to-guard-users)
        - [Using blade directives with multiple guards](#using-blade-directives-with-multiple-guards)

    - [Using a middleware](#using-a-middleware)
        - [Default Middleware](#default-middleware)
        - [Package Middleware](#package-middleware)

    - [Using artisan commands](#using-artisan-commands)
        - [Creating roles and permissions with Artisan Commands](#creating-roles-and-permissions-with-artisan-commands)
        - [Displaying roles and permissions in the console](#displaying-roles-and-permissions-in-the-console)
        - [Resetting the Cache](#resetting-the-cache)

- [Advanced usage](#advanced-usage)
    - [Unit testing](#unit-testing)
    - [Database Seeding](#database-seeding)
    - [Exceptions](#exceptions)

    - [Extending](#extending)
        - [Extending User Models](#extending-user-models)
        - [Extending Role and Permission Models](#extending-role-and-permission-models)
        - [Replacing Role and Permission Models](#replacing-role-and-permission-models)
        
    - [Migrations](#migrations)
        - [Adding fields to your models](#adding-fields-to-your-models)

    - [Cache](#cache)
        - [Manual cache reset](#manual-cache-reset)
        - [Cache Identifier](#cache-identifier)

    - [UUID](#uuid)
        - [Migrations](#uuid-migrations)
        - [Configuration (morph key)](#uuid-configuration)
        - [Models](#uuid-models)
        - [User Models](#uuid-user-models)

- [Best Practices](#best-practices)
    - [Roles vs Permissions](#roles-vs-permissions)
    - [Model Policies](#model-policies)

- [Miscellaneous](#miscellaneous)
    - [Testing](#testing)
    - [Questions and issues](#questions-and-issues)
    - [Changelog](#changelog)
    - [Contributing](#contributing)
    - [Security](#security)
    - [Credits](#credits)
    - [License](#license)

# Introduction

❓ What will you be able to do with the package once [installed](#installation)?:

### **Once installed you can do stuff like:**

```
✅ Give permissions to users.
```
```php
// Give general permission that you can manage and filter in your app.
$user->givePermissionTo('edit articles');

// Give a user a permission that is tied to a class.
$user->givePermissionTo('edit', Article::class);

// Give a user a permission to a model instance.
$article = Article::find(1);
$user->givePermissionTo('edit', $article); // OR
$user->givePermissionTo('edit', Article::class, 1); // OR
$user->givePermissionTo('edit', Article::class, $article->id);


// Give user permission to edit anything.
$user->givePermissionTo('edit'); //OR
$user->givePermissionTo('edit', '*');

// etc...
```

```
✅ Assign roles to users.
```

```php
// Assign user a role.
$user->assignRole('writer');

// Give permission to role
$role->givePermissionTo('edit articles');

$role->givePermissionTo('view', Article::class);

// etc...
```

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/authorization), you can check if a user has a permission with Laravel's default `can` function:

```php
$user->can('view', Article::class); //OR

$article = Article::find(1);
$user->can('view', $article); //OR
$user->can('view', Article::class, $article->id);
```

and Blade directives:

```php
@can('view', Article::class)
...
@endcan

// OR

@can('view', $article)
...
@endcan

// etc...
```

---




# Prerequisites

This package can be used in Laravel 5.8 or higher.

This package uses Laravel’s Gate layer to provide Authorization capabilities. The ```Gate/authorization``` layer requires that your ```User``` model implement the ```Illuminate\Contracts\Auth\Access\Authorizable``` contract. Otherwise the ```can()``` and ```authorize()``` methods will not work in your controllers, policies, templates, etc.

In the Installation instructions you’ll see that the ```HasLarakey``` trait must be added to the ```User``` model to enable this package’s features.

Thus, a typical basic ```User``` model would have these basic minimum requirements:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ghustavh97\Larakey\Traits\HasLarakey;

class User extends Authenticatable
{
    use HasLarakey;

    // ...
}
```

Additionally, your ```User``` model/object ***MUST NOT*** have a role or roles property (or field in the database), nor a ```roles()``` method on it. Those will interfere with the properties and methods added by the ```HasLarakey``` trait provided by this package, thus causing unexpected outcomes when this package’s methods are used to inspect roles and permissions.

Similarly, your ```User``` model/object ***MUST NOT*** have a permission or permissions property (or field in the database), nor a ```permissions()``` method on it. Those will interfere with the properties and methods added by the ```HasPermissions``` trait provided by this package (which is invoked via the ```HasLarakey``` trait).

This package publishes a ```config/larakey.php``` file. If you already have a file by that name, you must rename or remove it, as it will conflict with this package. You could optionally merge your own values with those required by this package, as long as the keys that this package expects are present. See the [source](https://github.com/Ghustavh97/larakey/blob/master/config/larakey.php) file for more details.

---




# Installation

This package can be used with Laravel 5.8 or higher.

1. Consult the [Prerequisites](#prerequisites) page for important considerations regarding your ```User``` models!

2. This package publishes a ```config/larakey.php``` file. If you already have a file by that name, you must rename or remove it.

3. You can install the package via composer: ```composer require ghustavh97/larakey```

4. Optional: The service provider will automatically get registered. Or you may manually add the service provider in your ```config/app.php``` file:

```php
'providers' => [
    // ...
    Ghustavh97\Larakey\LarakeyServiceProvider::class,
];
```

5. You should publish [the migration](https://github.com/Ghustavh97/larakey/blob/master/database/migrations/create_larakey_permission_tables.php.stub) and the ```config/larakey.php``` [config file](https://github.com/Ghustavh97/larakey/blob/master/config/larakey.php) with:

```bash
php artisan vendor:publish --provider="Ghustavh97\Larakey\LarakeyServiceProvider::class"
```

6. NOTE: If you are using UUIDs, see the [UUID](#uuid) section under [Advanced](#advanced) of the docs on steps before you continue. It explains some changes you may want to make to the migrations and config file before continuing. It also mentions important considerations after extending this package’s models for UUID capability.

7. Run the migrations: After the config and migration have been published and configured, you can create the tables for this package by running:

    ```bash
    php artisan migrate
    ```

8. Add the necessary trait to your ```User``` model: Consult the [Basic Usage](#basic-usage) section of the docs for how to get started using the features of this package.

---



## Default config file contents
You can view the default config file contents at:

https://github.com/Ghustavh97/larakey/blob/master/config/larakey.php

---



# Basic Usage

First, add the ```Ghustavh97\Larakey\Traits\HasLarakey``` trait to your ```User``` model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ghustavh97\Larakey\Traits\HasLarakey;

class User extends Authenticatable
{
    use HasLarakey;

    // ...
}
```

This package allows for users to be associated with permissions and roles. Every role is associated with multiple permissions. A ```Role``` and a ```Permission``` are regular Eloquent models. They require a name and can be created like this:

```php
use Ghustavh97\Larakey\Models\Role;
use Ghustavh97\Larakey\Models\Permission;

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

The ```role``` scope can accept a string, a ```\Ghustavh97\Larakey\Models\Role``` object or an ```\Illuminate\Support\Collection``` object.

The same trait also adds a scope to only get users that have a certain permission.

```php
$users = User::permission('edit articles')->get(); // Returns only users with the permission 'edit articles' (inherited or directly)
```

The scope can accept a string, a ```\Ghustavh97\Larakey\Models\Permission``` object or an ```\Illuminate\Support\Collection``` object.



---

## Eloquent
Since ```Role``` and ```Permission``` models are extended from Eloquent models, basic Eloquent calls can be used as well:
```php
$all_users_with_all_their_roles = User::with('roles')->get();
$all_users_with_all_direct_permissions = User::with('permissions')->get();
$all_roles_in_database = Role::all()->pluck('name');
```




---



## Using Permissions
---

### ***NOTE*** about using permission names in policies

When calling `authorize()` for a policy method, if you have a permission named the same as one of those policy methods, your permission "name" will take precedence and not fire the policy. For this reason it may be wise to avoid naming your permissions the same as the methods in your policy. While you can define your own method names, you can read more about the defaults Laravel offers in Laravel's documentation at https://laravel.com/docs/authorization#writing-policies



---

> ❕ The `givePermissionTo` and `revokePermissionTo` functions can accept a
string or a `Ghustavh97\Larakey\Models\Permission` object.

---

### Assigning Permissions <a id="giving-permissionss-example"></a>
> The function `givePermissionTo()` is used to give permissions to a user.
#### Description
```php
givePermissionTo(mixed $permission, [mixed $model = null, [mixed $modelId = null]]): bool
```
#### Arguments
- **$permission**
    - Type : `int` | `string` | `array` | `\Ghustavh97\Larakey\Contracts\Permission`
    - Description : The permission to give to the user.
- **$model**
    - Type : `string` | `\Illuminate\Database\Eloquent\Model`
    - Description : The model class or instance to be used with the permission to limit scope.
- **$modelId**
    - Type : `string` | `int`
    - Description : Used to indicate the id of a model when only a class name string is provided to `$model`. 
    - Note : ***`$model` must be present when this value is used.***
#### Returns
    Returns boolean.
#### Examples
***Give user permission***
```php
// Give permission
$user->givePermissionTo('edit'); // OR
$user->givePermissionTo('edit', '*');
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // TRUE
$user->hasPermissionTo('edit', '*'); // TRUE
$user->hasPermissionTo('edit', Post::class); // TRUE

$post = Post::find(1);
$user->hasPermissionTo('edit', $post); // TRUE
$user->hasPermissionTo('edit', Post::class, $post->id); // TRUE

$user->hasPermissionTo('edit', Comment::class); // TRUE

$comment = Comment::find(1);
$user->hasPermissionTo('edit', $post); // TRUE
$user->hasPermissionTo('edit', Comment::class, $comment->id); // TRUE
```
***Give user permission to class***
```php
// Give class permission
$user->givePermissionTo('edit', Post::class);
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // TRUE

$post = Post::find(1);
$user->hasPermissionTo('edit', $post); // TRUE
$user->hasPermissionTo('edit', Post::class, $post->id); // TRUE

$user->hasPermissionTo('edit', Comment::class); // FALSE

$comment = Comment::find(1);
$user->hasPermissionTo('edit', $comment); // FALSE
$user->hasPermissionTo('edit', Comment::class, $comment->id); // FALSE
```
***Give user permission to model instance***
```php
$post = Post::find(1);
$user->givePermissionTo('edit', $post); // OR;
$user->givePermissionTo('edit', Post::class, $post->id);
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // FALSE

$post = Post::find(1);
$user->hasPermissionTo('edit', $post); // TRUE
$user->hasPermissionTo('edit', Post::class, $post->id); // TRUE

$user->hasPermissionTo('edit', Comment::class); // FALSE

$comment = Comment::find(1);
$user->hasPermissionTo('edit', $comment); // FALSE
$user->hasPermissionTo('edit', Comment::class, $comment->id); // FALSE
```
***Give user multiple permissions at once***
```php
$user->givePermissionTo(['edit', 'delete', 'read']); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], '*'); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], Post::class); // OR;

$post = Post::find(1);
$user->givePermissionTo(['edit', 'delete', 'read'], $post); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], Post::class, $post->id); // OR;
```




---

### Revoking Permissions
> The function `revokePermissionTo()` is used to revoke/remove permissions from a user.
#### Description
```php
revokePermissionTo(mixed $permission, [mixed $model = null, mixed $modelId = null], [bool $recursive = false]): $this
```
#### Arguments
- **$permission**
    - Type : `int` | `string` | `array` | `\Ghustavh97\Larakey\Contracts\Permission`
    - Description : The permission to be removed from the user.
- **$model**
    - Type : `string` | `\Illuminate\Database\Eloquent\Model`
    - Description : The model class or instance to be used with the
- **$modelId**
    - Type : `string` | `int`
    - Description : Used to indicate the id of a model when only a class name string is provided to `$model`. 
    - Note : ***`$model` must be present when this value is used.***
- **$recursive**
    - Type : `boolean`
    - Description : Determines whether or not to revoke a permission recursively/also remove permissions with a lower scope.
#### Returns
    returns $this.
#### Examples <a id="revoking-permissionss-example"></a>
***Revoke permission from user***
```php
// Give permissions
$user->givePermissionTo('edit');
$user->givePermissionTo('edit', Post::class);
$user->givePermissionTo('edit', Post::class, 1);
```
```php
// Revoke permission
$user->revokePermissionTo('edit', '*');
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // TRUE
$user->hasPermissionTo('edit', Post::class, 1); // TRUE
```

 > ⚠️ **NOTE:** The user will still have permission to edit `Post::class` and `$post` with id 1. If you want to include permissions with a lower scope see [revoking permissions with recursion](#revoking-permissions-with-recursion).

***Revoke permission with class from user***
```php
// Give permission to class and model instance
$user->givePermissionTo('edit', Post::class);
$user->givePermissionTo('edit', Post::class, 1);
```
```php
// Give class permission
$user->revokePermissionTo('edit', Post::class);
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // TRUE

$post = Post::find(1);
$user->hasPermissionTo('edit', $post); // TRUE
$user->hasPermissionTo('edit', Post::class, $post->id); // TRUE
```
***Give user permission to model instance***
```php
$user->givePermissionTo('edit', Post::class, 1);

$post = Post::find(1);
$user->givePermissionTo('edit', $post); // OR;
$user->givePermissionTo('edit', Post::class, $post->id);
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // FALSE

$post = Post::find(1);
$user->hasPermissionTo('edit', $post); // TRUE
$user->hasPermissionTo('edit', Post::class, $post->id); // TRUE
```
***Give user multiple permission at once***
```php
$user->givePermissionTo(['edit', 'delete', 'read']); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], '*'); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], Post::class); // OR;

$post = Post::find(1);
$user->givePermissionTo(['edit', 'delete', 'read'], $post); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], Post::class, $post->id); // OR;
```

---


#### Revoking Permissions With Recursion
***Revoke permission from user (with recursion)***
Pass in a boolean of `true` in the `revokePermissionTo()` to revoke a permission with recursion. This will remove the permission with those with a lower scope that it.
#### Examples
```php
// Give user permissions
$user->givePermissionTo('edit');
$user->givePermissionTo('edit', Post::class);
$user->givePermissionTo('edit', Post::class, 1);
```
```php
// Revoke permission with recursion
$user->revokePermissionTo('edit', '*', true);
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // FALSE
$user->hasPermissionTo('edit', Post::class, 1); // FALSE
```


---

### Checking For Permissions
 > The function `hasPermissionTo()` OR `can()` can be used to check if a user has direct permission or permission via role.
#### Description
```php
hasPermissionTo(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```
#### Arguments
- **$permission**
    - Type : `int` | `string` | `array` | `\Ghustavh97\Larakey\Contracts\Permission`
    - Description : The permission to give to the user.
- **$model**
    - Type : `string` | `\Illuminate\Database\Eloquent\Model`
    - Description : The model class or instance to be used with the permission to limit scope.
- **$modelId**
    - Type : `string` | `int`
    - Description : Used to indicate the id of a model when only a class name string is provided to `$model`. 
    - Note : ***`$model` must be present when this value is used.***
- **$guard**
    - Type : `string`
    - Description : The guard to be used with `$permission`. 
#### Returns
    Returns boolean.
#### Examples
See [Assigning Permissions Examples](#giving-permissionss-example) OR [Revoking Permissions Examples](#revoking-permissionss-example)

You may pass an integer representing the permission id

```php
$user->hasPermissionTo('1');
$user->hasPermissionTo(Permission::find(1)->id);
$user->hasPermissionTo($somePermission->id);
```

Saved permissions will be registered with the `Illuminate\Auth\Access\Gate` class for the default guard. So you can
check if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit', Article::class);
```



---

### Checking For Direct Permissions
 > The function `hasDirectPermission()` can be used to check if a user has a direct permission.
#### Description
```php
hasDirectPermission(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```
#### Arguments
Are identical to [hasPermissionTo()](#checking-for-permissions)
#### Returns
    Returns boolean.
#### Examples
Is identical to [hasPermissionTo()](#checking-for-permissions)



---

### Checking For Permission Via Role
 > The function `hasPermissionViaRole()` can be used to check if a user has a role that has a permission.
#### Description
```php
hasPermissionViaRole(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```
#### Arguments
Are identical to [hasPermissionTo()](#checking-for-permissions)
#### Returns
    Returns boolean.
#### Examples
> You can read about roles [here](#using-roles)
```php
$role = Role::create(['name' => 'writer']);

$role->givePermissionTo(['create', 'edit', 'delete'], Article::class);

$user->hasPermissionViaRole('edit', Article::class); // FALSE

$user->assignRole('writer');

$user->hasPermissionViaRole('edit', Article::class); // TRUE
```






<!-- #### Checking for direct permissions

```php
// Check if the user has All direct permissions
$user->hasAllDirectPermissions(['edit articles', 'delete articles']);

// Check if the user has Any permission directly
$user->hasAnyDirectPermission(['create articles', 'delete articles']);
```
By following the previous example, when we call `$user->hasAllDirectPermissions(['edit articles', 'delete articles'])` 
it returns `true`, because the user has all these direct permissions. 
When we call
`$user->hasAnyDirectPermission('edit articles')`, it returns `true` because the user has one of the provided permissions.


You can list all of these permissions:

```php
// Direct permissions
$user->getDirectPermissions() // Or $user->permissions;

// Permissions inherited from the user's roles
$user->getPermissionsViaRoles();

// All permissions which apply on the user (inherited and direct)
$user->getAllPermissions();
```

All these responses are collections of `Ghustavh97\Larakey\Models\Permission` objects.



If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.

If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.


--- -->

<!-- Or revoke & add new permissions in one go:

```php
$user->syncPermissions(['edit articles', 'delete articles']);
```

You can check if a user has Any of an array of permissions:

```php
$user->hasAnyPermission(['edit articles', 'publish articles', 'unpublish articles']);
```

...or if a user has All of an array of permissions:

```php
$user->hasAllPermissions(['edit articles', 'publish articles', 'unpublish articles']);
```

You may also pass integers to lookup by permission id

```php
$user->hasAnyPermission(['edit articles', 1, 5]);
``` -->

## Using Roles
---

> ❕ The `assignRole`, `hasRole`, `hasAnyRole`, `hasAllRoles`  and `removeRole` functions can accept a
 string, a `\Ghustavh97\Larakey\Models\Role` object or an `\Illuminate\Support\Collection` object.



---

### Assigning Roles
 > The function `assignRole()` can be used to assign a role to a user.
#### Description
```php
assignRole(mixed $roles): $this
```
#### Arguments
- **$roles**
    - Type : `string` | `array` | `\Ghustavh97\Larakey\Contracts\Role`
    - Description : The roles to be assigned to the user.

#### Returns
    Returns $this.
#### Examples
```php
// Assign a role
$user->assignRole('writer');
// You can also assign multiple roles at once
$user->assignRole('writer', 'admin');
// or as an array
$user->assignRole(['writer', 'admin']);
```




---

### Revoking Roles
 > The function `removeRole()` can be used to revoke a role from a user.
#### Description
```php
removeRole(mixed $role): $this
```
#### Arguments
- **$role**
    - Type : `string` | `\Ghustavh97\Larakey\Contracts\Role`
    - Description : The role to remove from user.

#### Returns
    Returns $this.
#### Examples
```php
$user->removeRole('writer');
```




---

### Syncing Roles
 > The function `syncRoles()` can be used to sync roles on a user.
#### Description
```php
syncRoles(mixed $roles): $this
```
#### Arguments

- **$roles**
    - Type : `array` | `string` | `\Ghustavh97\Larakey\Contracts\Role`
    - Description : The roles to sync on user.

#### Returns
    Returns $this.
#### Examples
```php
// All current roles will be removed from the user and replaced by the array given
$user->syncRoles(['writer', 'admin']);
```




---

### Checking For Roles
 > The function `hasRole()` can be used to check if a user has a role(s).
#### Description
```php
hasRole(mixed $roles, [string $guard = null, bool $returnRole = false])
```
#### Arguments
- **$roles**
    - Type : `string` | `int` | `array` | `\Ghustavh97\Larakey\Contracts\Role|\Illuminate\Support\Collection`
    - Description : The role(s) to check.
- **$guard**
    - Type : `string`
    - Description : Guard to be used wit roles.
- **$returnRole**
    - Type : `bool`
    - Description : Return first matching role from user.

#### Returns
    Returns `bool` or `\Ghustavh97\Larakey\Contracts\Role`.
#### Examples
```php
$user->hasRole('writer');
// or at least one role from an array of roles:
$user->hasRole(['editor', 'moderator']);
```




---

### Checking For Any Role
 > The function `hasAnyRole()` can be used to check if a user has any of the given roles.
#### Description
```php
hasAnyRole(mixed $roles): bool
```
#### Arguments
- **$roles**
    - Type : `array` | `string` | `int` | `\Ghustavh97\Larakey\Contracts\Role` | `\Illuminate\Support\Collection`
    - Description : The roles to check.
#### Returns
    Returns bool.
#### Usage
```php
$user->hasAnyRole(['writer', 'reader']);
// or
$user->hasAnyRole('writer', 'reader');
```




---

### Checking For All Roles
 > The function `hasAllRoles()` can be used to check if a user has all the given roles.
#### Description
```php
hasAllRoles(mixed $roles, [string $guard = null]): bool
```
#### Arguments
- **$roles**
    - Type : `array` | `string` | `\Ghustavh97\Larakey\Contracts\Role`
    - Description : The roles to check.
#### Returns
    Returns bool.
#### Usage
```php
$user->hasAllRoles(Role::all());
```




---

### Using permissions via roles

> Any function found in the [Using Permissions](#using-permissions) can be used on a `Role` instance since it inherits the `HasPermissions` trait. So you can do stuff like:

```php

$role = Role::findByName('writer');

$role->givePermissionTo('edit articles');

$role->hasPermissionTo('edit articles');

$role->revokePermissionTo('edit articles');
```

The only diffrence is that we are using `$role` instead of `$user`.

---



## Blade directives

### Permissions
This package doesn't add any **permission**-specific Blade directives. 
Instead, use Laravel's native `@can` directive to check if a user has a certain permission.

```php
@can('edit articles')
  //
@endcan
```
or
```php
@if(auth()->user()->can('edit articles') && $some_other_condition)
  //
@endif
```

You can use `@can`, `@cannot`, `@canany`, and `@guest` to test for permission-related access.


### Roles 
As discussed in the Best Practices section of the docs, **it is strongly recommended to always use permission directives**, instead of role directives.

Additionally, if your reason for testing against Roles is for a Super-Admin, see the *Defining A Super-Admin* section of the docs.

If you actually need to test for Roles, this package offers some Blade directives to verify whether the currently logged in user has all or any of a given list of roles. 

Optionally you can pass in the `guard` that the check will be performed on as a second argument.

#### Blade and Roles
Check for a specific role:
```php
@role('writer')
    I am a writer!
@else
    I am not a writer...
@endrole
```
is the same as
```php
@hasrole('writer')
    I am a writer!
@else
    I am not a writer...
@endhasrole
```

Check for any role in a list:
```php
@hasanyrole($collectionOfRoles)
    I have one or more of these roles!
@else
    I have none of these roles...
@endhasanyrole
// or
@hasanyrole('writer|admin')
    I am either a writer or an admin or both!
@else
    I have none of these roles...
@endhasanyrole
```
Check for all roles:

```php
@hasallroles($collectionOfRoles)
    I have all of these roles!
@else
    I do not have all of these roles...
@endhasallroles
// or
@hasallroles('writer|admin')
    I am both a writer and an admin!
@else
    I do not have all of these roles...
@endhasallroles
```

Alternatively, `@unlessrole` gives the reverse for checking a singular role, like this:

```php
@unlessrole('does not have this role')
    I do not have the role
@else
    I do have the role
@endunlessrole
```

## Defining a Super-Admin

We strongly recommend that a Super-Admin be handled by setting a global `Gate::before` or `Gate::after` rule which checks for the desired role. 

Then you can implement the best-practice of primarily using permission-based controls (@can and $user->can, etc) throughout your app, without always having to check for "is this a super-admin" everywhere. Best not to use role-checking (ie: `hasRole`) when you have Super Admin features like this.


### `Gate::before`
If you want a "Super Admin" role to respond `true` to all permissions, without needing to assign all those permissions to a role, you can use Laravel's `Gate::before()` method. For example:

```php
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
```

NOTE: `Gate::before` rules need to return `null` rather than `false`, else it will interfere with normal policy operation. [See more.](https://laracasts.com/discuss/channels/laravel/policy-gets-never-called#reply=492526)

Jeffrey Way explains the concept of a super-admin (and a model owner, and model policies) in the [Laravel 6 Authorization Filters](https://laracasts.com/series/laravel-6-from-scratch/episodes/51) video and some related lessons in that chapter.


### `Gate::after`

Alternatively you might want to move the Super Admin check to the `Gate::after` phase instead, particularly if your Super Admin shouldn't be allowed to do things your app doesn't want "anyone" to do, such as writing more than 1 review, or bypassing unsubscribe rules, etc.

The following code snippet is inspired from [Freek's blog article](https://murze.be/when-to-use-gateafter-in-laravel) where this topic is discussed further.

```php
// somewhere in a service provider

Gate::after(function ($user, $ability) {
   return $user->hasRole('Super Admin'); // note this returns boolean
});
```

## Using multiple guards

When using the default Laravel auth configuration all of the core methods of this package will work out of the box, no extra configuration required.

However, when using multiple guards they will act like namespaces for your permissions and roles. Meaning every guard has its own set of permissions and roles that can be assigned to their user model.

### The Downside To Multiple Guards

Note that this package requires you to register a permission name for each guard you want to authenticate with. So, "edit-article" would have to be created multiple times for each guard your app uses. An exception will be thrown if you try to authenticate against a non-existing permission+guard combination. Same for roles.

> **Tip**: If your app uses only a single guard, but is not `web` (Laravel's default, which shows "first" in the auth config file) then change the order of your listed guards in your `config/auth.php` to list your primary guard as the default and as the first in the list of defined guards. While you're editing that file, best to remove any guards you don't use, too.


### Using permissions and roles with multiple guards

When creating new permissions and roles, if no guard is specified, then the **first** defined guard in `auth.guards` config array will be used. 

```php
// Create a manager role for users authenticating with the admin guard:
$role = Role::create(['guard_name' => 'admin', 'name' => 'manager']);

// Define a `publish articles` permission for the admin users belonging to the admin guard
$permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles']);

// Define a *different* `publish articles` permission for the regular users belonging to the web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => 'publish articles']);
```

To check if a user has permission for a specific guard:

```php
$user->hasPermissionTo('publish articles', 'admin');
```

> **Note**: When determining whether a role/permission is valid on a given model, it checks against the first matching guard in this order (it does NOT check role/permission for EACH possibility, just the first match):
- first the guardName() method if it exists on the model;
- then the `$guard_name` property if it exists on the model;
- then the first-defined guard/provider combination in the `auth.guards` config array that matches the logged-in user's guard;
- then the `auth.defaults.guard` config (which is the user's guard if they are logged in, else the default in the file).


### Assigning permissions and roles to guard users

You can use the same core methods to assign permissions and roles to users; just make sure the `guard_name` on the permission or role matches the guard of the user, otherwise a `GuardDoesNotMatch` or `Role/PermissionDoesNotExist` exception will be thrown.


### Using blade directives with multiple guards

You can use all of the blade directives offered by this package by passing in the guard you wish to use as the second argument to the directive:

```php
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```

## Using a middleware

### Default Middleware

For checking against a single permission (see Best Practices) using `can`, you can use the built-in Laravel middleware provided by `\Illuminate\Auth\Middleware\Authorize::class` like this:

```php
Route::group(['middleware' => ['can:publish articles']], function () {
    //
});
```

### Package Middleware

This package comes with `RoleMiddleware`, `PermissionMiddleware` and `RoleOrPermissionMiddleware` middleware. You can add them inside your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    // ...
    'role' => \Ghustavh97\Larakey\Middlewares\RoleMiddleware::class,
    'permission' => \Ghustavh97\Larakey\Middlewares\PermissionMiddleware::class,
    'role_or_permission' => \Ghustavh97\Larakey\Middlewares\RoleOrPermissionMiddleware::class,
];
```

Then you can protect your routes using middleware rules:

```php
Route::group(['middleware' => ['role:super-admin']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role:super-admin','permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:super-admin|edit articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:publish articles']], function () {
    //
});
```

Alternatively, you can separate multiple roles or permission with a `|` (pipe) character:

```php
Route::group(['middleware' => ['role:super-admin|writer']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles|edit articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:super-admin|edit articles']], function () {
    //
});
```

You can protect your controllers similarly, by setting desired middleware in the constructor:

```php
public function __construct()
{
    $this->middleware(['role:super-admin','permission:publish articles|edit articles']);
}
```

```php
public function __construct()
{
    $this->middleware(['role_or_permission:super-admin|edit articles']);
}
```

## Using artisan commands

### Creating roles and permissions with Artisan Commands

You can create a role or permission from the console with artisan commands.

```bash
php artisan permission:create-role writer
```

```bash
php artisan permission:create-permission "edit articles"
```

When creating permissions/roles for specific guards you can specify the guard names as a second argument:

```bash
php artisan permission:create-role writer web
```

```bash
php artisan permission:create-permission "edit articles" web
```

When creating roles you can also create and link permissions at the same time:

```bash
php artisan permission:create-role writer web "create articles|edit articles"
```

### Displaying roles and permissions in the console

There is also a `show` command to show a table of roles and permissions per guard:

```bash
php artisan permission:show
```

### Resetting the Cache

When you use the built-in functions for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record.

See the Advanced-Usage/Cache section of these docs for detailed specifics.

If you need to manually reset the cache for this package, you may use the following artisan command:

```bash
php artisan permission:cache-reset
```

Again, it is more efficient to use the API provided by this package, instead of manually clearing the cache.

# Advanced usage

## Unit testing

In your application's tests, if you are not seeding roles and permissions as part of your test `setUp()` then you may run into a chicken/egg situation where roles and permissions aren't registered with the gate (because your tests create them after that gate registration is done). Working around this is simple: In your tests simply add a `setUp()` instruction to re-register the permissions, like this:

```php
    public function setUp(): void
    {
        // first include all the normal setUp operations
        parent::setUp();

        // now re-register all the roles and permissions
        $this->app->make(\Ghustavh97\Larakey\Padlock\Gate::class)->registerPermissions();
    }
```

## Database Seeding

You may discover that it is best to flush this package's cache before seeding, to avoid cache conflict errors. This can be done directly in a seeder class. Here is a sample seeder, which first clears the cache, creates permissions and then assigns permissions to roles (the order of these steps is intentional):

```php
use Illuminate\Database\Seeder;
use Ghustavh97\Larakey\Models\Role;
use Ghustavh97\Larakey\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Ghustavh97\Larakey\Padlock\Cache::class]->forgetCachedPermissions();

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

## Exceptions

If you need to override exceptions thrown by this package, you can simply use normal [Laravel practices for handling exceptions](https://laravel.com/docs/errors#render-method).

An example is shown below for your convenience, but nothing here is specific to this package other than the name of the exception.

You can find all the exceptions added by this package in the code here: https://github.com/ghustavh97/larakey/tree/master/src/Exceptions


**app/Exceptions/Handler.php**
```php
public function render($request, Throwable $exception)
{
    if ($exception instanceof \Ghustavh97\Larakey\Exceptions\UnauthorizedException) {
        return response()->json([
            'responseMessage' => 'You do not have the required authorization.',
            'responseStatus'  => 403,
        ]);
    }

    return parent::render($request, $exception);
}
```

---
## Extending


### Extending User Models
Laravel's authorization features are available in models which implement the `Illuminate\Foundation\Auth\Access\Authorizable` trait. By default Laravel does this in `\App\User` by extending `Illuminate\Foundation\Auth\User`, in which the trait and `Illuminate\Contracts\Auth\Access\Authorizable` contract are declared.

If you are creating your own User models and wish Authorization features to be available, you need to implement `Illuminate\Contracts\Auth\Access\Authorizable` in one of those ways as well.


### Extending Role and Permission Models
If you are extending or replacing the role/permission models, you will need to specify your new models in this package's `config/larakey.php` file. 

First be sure that you've published the configuration file (see the Installation instructions), and edit it to update the `models.role` and `models.permission` values to point to your new models.

Note the following requirements when extending/replacing the models: 

If you need to EXTEND the existing `Role` or `Permission` models note that:

- Your `Role` model needs to extend the `Ghustavh97\Larakey\Models\Role` model
- Your `Permission` model needs to extend the `Ghustavh97\Larakey\Models\Permission` model

### Replacing Role and Permission Models
If you need to REPLACE the existing `Role` or `Permission` models you need to keep the following things in mind:

- Your `Role` model needs to implement the `Ghustavh97\Larakey\Contracts\Role` contract
- Your `Permission` model needs to implement the `Ghustavh97\Larakey\Contracts\Permission` contract

## Migrations

### Adding fields to your models

You can add your own migrations to make changes to the role/permission tables, as you would for adding/changing fields in any other tables in your Laravel project.
Following that, you can add any necessary logic for interacting with those fields ... to your custom/extended Models.

---
## Cache

Role and Permission data are cached to speed up performance.

While we recommend not changing the cache "key" name, if you wish to alter the expiration time you may do so in the `config/larakey.php` file, in the `cache` array.

When you use the built-in functions for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record:

```php
$user->assignRole('writer');
$user->removeRole('writer');
$user->syncRoles(params);
$role->givePermissionTo('edit articles');
$role->revokePermissionTo('edit articles');
$role->syncPermissions(params);
$permission->assignRole('writer');
$permission->removeRole('writer');
$permission->syncRoles(params);
```

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then you will not see the changes reflected in the application unless you manually reset the cache.

### Manual cache reset
To manually reset the cache for this package, you can run the following in your app code:
```php
app()->make(\Ghustavh97\Larakey\Padlock\Cache::class)->forgetCachedPermissions();
```

Or you can use an Artisan command:
```bash
php artisan permission:cache-reset
```


### Cache Identifier

TIP: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites 
running on your server, you could run into cache clashes between apps. It is prudent to set your own 
cache `prefix` in Laravel's `/config/cache.php` to something unique for each application. 
This will prevent other applications from accidentally using/changing your cached data.

---
## UUID

If you're using UUIDs or GUIDs for your User models there are a few considerations which various users have contributed. As each UUID implementation approach is different, some of these may or may not benefit you. As always, your implementation may vary.

### Migrations <a id="uuid-migrations"></a>
You will probably want to update the `create_permission_tables.php` migration:

- Replace `$table->unsignedBigInteger($columnNames['model_morph_key'])` with `$table->uuid($columnNames['model_morph_key'])`.


### Configuration (morph key) <a id="uuid-configuration"></a>
You will probably also want to update the configuration `column_names.model_morph_key`:

- Change to `model_uuid` instead of the default `model_id`. (The default of `model_id` is shown in this snippet below. Change it to match your needs.)

        'column_names' => [    
            /*
             * Change this if you want to name the related model primary key other than
             * `model_id`.
             *
             * For example, this would be nice if your primary keys are all UUIDs. In
             * that case, name this `model_uuid`.
             */
            'model_morph_key' => 'model_id',
        ],

### Models <a id="uuid-models"></a>
You will probably want to Extend the default Role and Permission models into your own namespace, to set some specific properties (see the Extending section of the docs):

- You may want to set `protected $keyType = "string";` so Laravel doesn't cast it to integer.
- You may want to set `protected $primaryKey = 'guid';` (or `uuid`, etc) if you changed the column name in your migrations.
- Optional: Some people have reported value in setting `public $incrementing = false;`, but others have said this caused them problems. Your implementation may vary.

### User Models <a id="uuid-user-models"></a>
Troubleshooting tip: In the ***Prerequisites*** section of the docs we remind you that your User model must implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract so that the Gate features are made available to the User object.
In the default User model provided with Laravel, this is done by extending another model (aliased to `Authenticatable`), which extends the base Eloquent model. However, your UUID implementation may need to override that in order to set some of the properties mentioned in the Models section above. If you are running into difficulties, you may want to double-check whether your User model is doing UUIDs consistent with other parts of your app.

## Best Practices

### Roles vs Permissions

It is generally best to code your app around `permissions` only. That way you can always use the native Laravel `@can` and `can()` directives everywhere in your app.

Roles can still be used to group permissions for easy assignment, and you can still use the role-based helper methods if truly necessary. But most app-related logic can usually be best controlled using the `can` methods, which allows Laravel's Gate layer to do all the heavy lifting.

eg: `users` have `roles`, and `roles` have `permissions`, and your app always checks for `permissions`, not `roles`.

### Model Policies

The best way to incorporate access control for application features is with [Laravel's Model Policies](https://laravel.com/docs/authorization#creating-policies).

Using Policies allows you to simplify things by abstracting your "control" rules into one place, where your application logic can be combined with your permission rules.

Jeffrey Way explains the concept simply in the [Laravel 6 Authorization Filters](https://laracasts.com/series/laravel-6-from-scratch/episodes/51) video and some related lessons in that chapter. He also mentions how to set up a super-admin, both in a model policy and globally in your application.

Here is an example of implementing a model policy with this package.

```php
<?php

namespace App\Policies;

use App\Post;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the post.
     *
     * @param  \App\User|null $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function view(?User $user, Post $post)
    {
        if ($post->published) {
            return true;
        }

        // visitors cannot view unpublished items
        if ($user === null) {
            return false;
        }

        // admin overrides published status
        if ($user->can('view unpublished posts')) {
            return true;
        }

        // authors can view their own unpublished posts
        return $user->id == $post->user_id;
    }

    /**
     * Determine whether the user can create posts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can('create posts')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        if ($user->can('edit own posts')) {
            return $user->id == $post->user_id;
        }

        if ($user->can('edit all posts')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        if ($user->can('delete own posts')) {
            return $user->id == $post->user_id;
        }

        if ($user->can('delete any post')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function restore(User $user, Post $post)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function forceDelete(User $user, Post $post)
    {
        //
    }
}
```

# Miscellaneous

## Testing

``` bash
composer test
```

## Questions and issues

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the package? Feel free to [create an issue on GitHub](https://github.com/Ghustavh97/larakey/issues), we’ll try to address it as soon as possible.

If you’ve found a bug regarding security please mail ghustavh97@gmail.com instead of using the issue tracker.

## Changelog

Please see [CHANGELOG](https://github.com/Ghustavh97/larakey/blob/master/CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email [ghustavh97@gmail.com](mailto:ghustavh97@gmail.com) instead of using the issue tracker.

## Credits

This package is a fork of [laravel-permissions](https://github.com/spatie/laravel-permission)

Massive thanks to [Freek Van der Herten](https://github.com/freekmurze) and [All Contributors](../../contributors) for making such an awesome package.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
