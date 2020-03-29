# Revoking Roles

 > The function `removeRole()` can be used to revoke a role from a user.

## Description

```php
removeRole(mixed $role): $this
```

### Arguments

- ***$role***
    - Type : `string` | `\Oslllo\Larakey\Contracts\Role`
    - Description : The role to remove from user.

#### Returns

Returns `$this`.

---

#### Examples

```php
$user->removeRole('writer');
```

---
