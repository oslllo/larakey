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
