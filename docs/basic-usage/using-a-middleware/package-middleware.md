## Package Middleware

This package comes with `RoleMiddleware`, `PermissionMiddleware` and `RoleOrPermissionMiddleware` middleware. You can add them inside your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    // ...
    'role' => \Oslllo\Larakey\Middlewares\RoleMiddleware::class,
    'permission' => \Oslllo\Larakey\Middlewares\PermissionMiddleware::class,
    'role_or_permission' => \Oslllo\Larakey\Middlewares\RoleOrPermissionMiddleware::class,
];
```

>Then you can protect your routes using middleware rules:

```php
Route::group(['middleware' => ['role:super-admin']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role:super-admin','permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:super-admin|edit articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:publish articles']], function () {
    //
});
```

>Alternatively, you can separate multiple roles or permission with a `|` (pipe) character:

```php
Route::group(['middleware' => ['role:super-admin|writer']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles|edit articles']], function () {
    //
});

Route::group(['middleware' => ['role_or_permission:super-admin|edit articles']], function () {
    //
});
```

>You can protect your controllers similarly, by setting desired middleware in the constructor:

```php
public function __construct()
{
    $this->middleware(['role:super-admin','permission:publish articles|edit articles']);
}
```

```php
public function __construct()
{
    $this->middleware(['role_or_permission:super-admin|edit articles']);
}
```

---
