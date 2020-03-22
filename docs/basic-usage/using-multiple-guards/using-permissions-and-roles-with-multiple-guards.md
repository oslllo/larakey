# <u>Using permissions and roles with multiple guards</u>

When creating new permissions and roles, if no guard is specified, then the **first** defined guard in `auth.guards` config array will be used. 

```php
// Create a manager role for users authenticating with the admin guard:
$role = Role::create(['guard_name' => 'admin', 'name' => 'manager']);

// Define a `publish articles` permission for the admin users belonging to the admin guard
$permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles']);

// Define a *different* `publish articles` permission for the regular users belonging to the web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => 'publish articles']);
```

>To check if a user has permission for a specific guard:

```php
$user->hasPermissionTo('publish articles', 'admin');
```

> **Note**: When determining whether a role/permission is valid on a given model, it checks against the first matching guard in this order (it does NOT check role/permission for EACH possibility, just the first match):
- first the guardName() method if it exists on the model;
- then the `$guard_name` property if it exists on the model;
- then the first-defined guard/provider combination in the `auth.guards` config array that matches the logged-in user's guard;
- then the `auth.defaults.guard` config (which is the user's guard if they are logged in, else the default in the file).
