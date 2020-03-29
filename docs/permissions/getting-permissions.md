# Getting Permissions

* [Get All Permissions](#get-all-permissions)
* [Get Direct Permissions](#get-direct-permissions)
* [Get Permission Role](#get-permission-role)
* [Get Permissions Via Role](#get-permissions-via-roles)

## Get All Permissions

 > The function `getAllPermissions()` can be used to return all permissions coupled to the model.

## Description

```php
getAllPermissions(): \Illuminate\Support\Collection
```

### Arguments

`none`

#### Returns

Returns `\Illuminate\Support\Collection`.

---

## Examples

```php
// Get all permissions
$user = User::find(1);
$user->getAllPermissions(); // OR

$role = Role::find(1);
$role->getAllPermissions();
```

---

## Get Direct Permissions

 > The function `getDirectPermissions()` can be used to return all direct permissions coupled to the model.

## Description

```php
getDirectPermissions(): \Illuminate\Support\Collection
```

### Arguments

`none`

#### Returns

Returns `\Illuminate\Support\Collection`.

---

## Examples

```php
// Get direct permissions
$user = User::find(1);
$user->getDirectPermissions(); // OR

$role = Role::find(1);
$role->getDirectPermissions();
```

---

## Get Permission Role

 > The function `getPermissionRole()` can be used to the first permission role. Returns new `Role` instance if none are found.

## Description

```php
getPermissionRole(mixed $permission): \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
```

### Arguments

* ***$permission***
    * Type : `\Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Models\Permission`
    * Description : The permission to use.

#### Returns

Returns `\Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role`.

## Examples

```php
$permission = Permission::find(1);

$user->getPermissionRole($permission);
```

---

## Get Permissions Via Roles

 > The function `getPermissionsViaRoles()` can be used to return all the permissions the model has via roles.

## Description

```php
getPermissionsViaRoles(): \Illuminate\Support\Collection
```

### Arguments

`none`

#### Returns

Returns `\Illuminate\Support\Collection`.

---

## Examples

```php
// Get direct permissions
$user = User::find(1);
$user->getPermissionsViaRoles();
```

---
