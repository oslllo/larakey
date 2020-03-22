
# <u>Assigning Permissions</u>

> The function `givePermissionTo()` is used to give permissions to a user.
#### Description
```php
givePermissionTo(mixed $permission, [mixed $model = null, [mixed $modelId = null]]): bool
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

#### Returns
Returns `boolean`.

---

## Examples

>Give user permission (to any class or model instance).

```php
// Give permission to edit 'anything'.
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

---

>Give user permission to a class.
```php
// Give user permission to edit any post.
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

---

> Give user permission to a model instance.
```php
// Give user permission to edit this post
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

---

> Give user multiple permissions at once
```php
// Give user multiple permissions to post class
$user->givePermissionTo(['edit', 'delete', 'read']); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], '*'); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], Post::class); // OR;

// Give user multiple permissions to a single post
$post = Post::find(1);
$user->givePermissionTo(['edit', 'delete', 'read'], $post); // OR;
$user->givePermissionTo(['edit', 'delete', 'read'], Post::class, $post->id); // OR;
```
