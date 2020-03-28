# <u>Sync Permissions</u>

 > The function `syncPermissions()` can be used to remove all current permissions and set the given ones.

## Description

```php
syncPermissions(array $permissions): bool
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

// Remove all current permissions and set the given ones.
$user->syncPermissions([
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
