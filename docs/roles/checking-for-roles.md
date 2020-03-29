# Checking For Roles

* [Checking For Any Role](#checking-for-any-role)
* [Checking For All Roles](#checking-for-all-roles)

> The function `hasRole()` can be used to check if a user has a role(s).

## Description

```php
hasRole(mixed $roles, [string $guard = null, bool $returnRole = false])
```

### Arguments

* ***$roles***
    * Type : `string` | `int` | `array` | `\Oslllo\Larakey\Contracts\Role|\Illuminate\Support\Collection`
    * Description : The role(s) to check.
* ***$guard***
    * Type : `string`
    * Description : Guard to be used wit roles.
* ***$returnRole***
    * Type : `bool`
    * Description : Return first matching role from user.

#### Returns

Returns `bool` or `\Oslllo\Larakey\Contracts\Role`.

---

## Examples

```php
$user->hasRole('writer');
// or at least one role from an array of roles:
$user->hasRole(['editor', 'moderator']);
```

---

## Checking For Any Role

> The function `hasAnyRole()` can be used to check if a user has any of the given roles.

### Description

```php
hasAnyRole(mixed $roles): bool
```

#### Arguments

* **$roles**
    * Type : `array` | `string` | `int` | `\Oslllo\Larakey\Contracts\Role` | `\Illuminate\Support\Collection`
    * Description : The roles to check.

#### Returns

Returns `bool`.

---

## Examples

```php
$user->hasAnyRole(['writer', 'reader']);
// or
$user->hasAnyRole('writer', 'reader');
```

---

## Checking For All Roles

 > The function `hasAllRoles()` can be used to check if a user has all the given roles.

### Description

```php
hasAllRoles(mixed $roles, [string $guard = null]): bool
```

#### Arguments

* ***$roles***
    * Type : `array` | `string` | `\Oslllo\Larakey\Contracts\Role`
    * Description : The roles to check.

#### Returns

Returns `bool`.

---

## Examples

```php
$user->hasAllRoles(Role::all());
```

---
