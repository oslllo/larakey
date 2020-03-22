# <u>Introduction</u>

## Features

Once [installed](/installation/installation.md) you can:


>✅ GIve user permission (to any class or model instance).

```php
// Give general permission that you can manage and filter in your app.
$user->givePermissionTo('edit articles'); // OR
$user->givePermissionTo('edit articles', '*') // Explicitly saying that they can edit any article;
```

---


>✅ Give user permission to a class.

```php
// Give a user a permission that is tied to a class.
$user->givePermissionTo('edit', Article::class);
```

---

>✅ GIve permission to a model instance.

```php
// Give a user a permission to a model instance.
$article = Article::find(1);
$user->givePermissionTo('edit', $article); // OR
$user->givePermissionTo('edit', Article::class, 1); // OR
$user->givePermissionTo('edit', Article::class, $article->id);
```

---

>✅ Assign roles to users.


```php
// Assign user a role.
$user->assignRole('writer');

// Give permission to role
$role->givePermissionTo('view', Article::class);
```

---

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/authorization), you can check if a user has a permission with Laravel's default `can` function:

```php
// Check if user has permission to view
$user->can('view', Article::class); //OR

// Check if user has permission to view this article
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
```

---
