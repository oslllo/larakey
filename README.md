# Larakey - (Permissions and Roles For Laravel)

![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/oslllo/larakey?include_prereleases)
![Travis (.org) branch](https://img.shields.io/travis/oslllo/larakey/master?label=Travis%20CI)
![GitHub](https://img.shields.io/github/license/Oslllo/larakey)
![GitHub issues](https://img.shields.io/github/issues/Oslllo/larakey)
![GitHub closed issues](https://img.shields.io/github/issues-closed/Oslllo/larakey)

## Features

- Assign permissions and roles to users.
- Assign a permission to a class to a user.
- Assign a permission to a model instances to a user.
- Permission caching.

# Documentation, Installation, and Usage Instructions


# Testing

``` bash
composer test
```

# Questions and issues

For any questions and issues feel free to [create an issue on GitHub](https://github.com/Oslllo/larakey/issues), we’ll try to address it as soon as possible.

If you’ve found a bug regarding security please mail ghustavh97@gmail.com instead of using the issue tracker.

# Changelog

Please see [CHANGELOG](https://github.com/Oslllo/larakey/blob/master/CHANGELOG.md) for more information what has changed recently.

# Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

# Security

If you discover any security-related issues, please email [ghustavh97@gmail.com](mailto:ghustavh97@gmail.com) instead of using the issue tracker.

# Credits

This package is a fork of [laravel-permissions](https://github.com/spatie/laravel-permission) by [Freek Van der Herten](https://github.com/freekmurze) and [All Contributors](https://github.com/spatie/laravel-permission/contributors).

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

<!-- Or revoke & add new permissions in one go:

```php
$user->syncPermissions(['edit articles', 'delete articles']);
```

You can check if a user has Any of an array of permissions:

```php
$user->hasAnyPermission(['edit articles', 'publish articles', 'unpublish articles']);
```

...or if a user has All of an array of permissions:

```php
$user->hasAllPermissions(['edit articles', 'publish articles', 'unpublish articles']);
```

You may also pass integers to lookup by permission id

```php
$user->hasAnyPermission(['edit articles', 1, 5]);
``` -->




