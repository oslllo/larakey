# <u>Checking For Any Permissions</u>

 > The function `hasAnyPermission()` can be used to check if a user has any of the permissions.

## Description

```php
hasAnyPermission(array $permissions): bool
```

### Arguments

- ***$permissions***
    - Type :  `array`
    - Description : The array of permissions to check.

#### Returns

Returns `boolean`.

## Examples

```php
$post = Post::find(1);

$user->hasAnyPermission([
    'view',
    ['view', '*'],
    ['create', Post::class],
    ['edit', Post::class, 1],
    ['delete', $this->testUserPost],
    [['view', 'create', 'edit', 'delete'], Post::class, 1],
    [['view', 'create', 'edit', 'delete'], $this->testUserPost],
    [['view', 'create', 'edit', 'delete'], $this->testUserPost, 'web']
]);

```

---
