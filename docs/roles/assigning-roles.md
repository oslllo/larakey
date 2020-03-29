# Assigning Roles

* [Syncing Roles](#syncing-roles)

 > The function `assignRole()` can be used to assign a role to a user.

## Description

```php
assignRole(mixed $roles): $this
```

### Arguments

* ***$roles***
    * Type : `string` | `array` | `\Oslllo\Larakey\Contracts\Role`
    * Description : The roles to be assigned to the user.

#### Returns

Returns `$this`.

---

## Examples

```php
// Assign a role
$user->assignRole('writer');
// You can also assign multiple roles at once
$user->assignRole('writer', 'admin');
// or as an array
$user->assignRole(['writer', 'admin']);
```

---

## Syncing Roles

 > The function `syncRoles()` can be used to sync roles on a user.

### Description

```php
syncRoles(mixed $roles): $this
```

#### Arguments

* ***$roles***
    * Type : `array` | `string` | `\Oslllo\Larakey\Contracts\Role`
    * Description : The roles to sync on user.

#### Returns

Returns `$this`.

---

## Examples

```php
// All current roles will be removed from the user and replaced by the array given
$user->syncRoles(['writer', 'admin']);
```

---
