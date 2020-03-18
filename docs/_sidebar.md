* [Home](/)

* [Introduction](introduction.md)

* [Prerequisites](prerequisites.md)

* Installation
    * [Installation](installation/installation.md)
    * [Default config file contents](installation/default-config-file-contents.md)

* Basic Usage
    * [Basic Usage](basic-usage.md)
    * [Eloquent](basic-usage/eloquent.md)

    * Using Permissions
        * [⚠️ Using Permissions](basic-usage/using-permissions.md)
        * [Assigning Permissions](basic-usage/using-permissions/assigning-permissions.md)
        * [Revoking Permissions](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md)
            * [❕ With Recursion](basic-usage/using-permissions/revoking-permissions/with-recursion.md)
        * [Checking For Permissions](basic-usage/using-permissions/checking-for-permissions.md)
        * [Checking For Direct Permissions](basic-usage/using-permissions/checking-for-direct-permissions.md)
        * [Checking For Any Direct Permission](basic-usage/using-permissions/checking-for-any-direct-permission.md)
        * [Get Direct Permission](basic-usage/using-permissions/get-direct-permissions.md)
        * [Get Permissions Via Roles](basic-usage/using-permissions/get-permissions-via-roles.md)
        * [Get All Permissions](basic-usage/using-permissions/get-all-permissions.md)

    * Using Roles
        * [Using Roles](basic-usage/using-roles.md)
        * [Assigning Roles](basic-usage/using-roles/assigning-roles.md)
        * [Revoking Roles](basic-usage/using-roles/revoking-roles.md)
        * [Syncing Roles](basic-usage/using-roles/syncing-roles.md)
        * [Checking For Roles](basic-usage/using-roles/checking-for-roles.md)
        * [Checking For Any Role](basic-usage/using-roles/checking-for-any-role.md)
        * [Checking For All Roles](basic-usage/using-roles/checking-for-all-roles.md)
        * [Using permissions via roles](basic-usage/using-roles/using-permissions-via-roles.md)

    * Blade directives
        * [Blade Permissions](basic-usage/blade-directives/blade-permissions.md)
        * [Blade Roles](basic-usage/blade-directives/blade-roles.md)

    * Defining a Super-Admin
        * [Defining a Super-Admin](basic-usage/defining-a-super-admin.md)

    * Using multiple guards
        * [Using multiple guards](basic-usage/using-multiple-guards.md)
        * [The Downside To Multiple Guards](basic-usage/using-multiple-guards/the-downside-to-multiple-guards.md)
        * [Using permissions and roles with multiple guards](basic-usage/using-multiple-guards/using-permissions-and-roles-with-multiple-guards.md)
        * [Assigning permissions and roles to guard users](basic-usage/using-multiple-guards/assigning-permissions-and-roles-to-guard-users.md)
        * [Using blade directives with multiple guards](basic-usage/using-multiple-guards/using-blade-directives-with-multiple-guards.md)

    * Using a middleware
        * [Default Middleware](basic-usage/using-a-middleware/default-middleware.md)
        * [Package Middleware](basic-usage/using-a-middleware/package-middleware.md)

    * Using artisan commands
        * [Creating roles and permissions with Artisan Commands](basic-usage/using-artisan-commands/creating-roles-and-permissions-with-artisan-commands.md)
        * [Displaying roles and permissions in the console](basic-usage/using-artisan-commands/displaying-roles-and-permissions-in-the-console.md)
        * [Resetting the Cache](basic-usage/using-artisan-commands/resetting-the-cache.md)

* Advanced usage
    * [Unit testing](advanced-usage/unit-testing.md)
    * [Database Seeding](advanced-usage/database-seeding.md)
    * [Exceptions](advanced-usage/exceptions.md)

    * Extending
        * [Extending User Models](advanced-usage/extending/extending-user-models.md)
        * [Extending Role and Permission Models](advanced-usage/extending/extending-role-and-permission-models.md)
        * [Replacing Role and Permission Models](advanced-usage/extending/replacing-role-and-permission-models.md)
        
    * Migrations
        * [Adding fields to your models](advanced-usage/migrations/adding-fields-to-your-models.md)

    * Cache
        * [Cache](advanced-usage/cache/cache.md)
        * [Manual cache reset](advanced-usage/cache/manual-cache-reset.md)
        * [Cache Identifier](advanced-usage/cache/cache-identifier.md)

    * UUID
        * [UUID](advanced-usage/uuid/uuid.md)
        * [Migrations](advanced-usage/uuid/uuid-migrations.md)
        * [Configuration (morph key)](advanced-usage/uuid/uuid-configuration.md)
        * [Models](advanced-usage/uuid/uuid-models.md)
        * [User Models](advanced-usage/uuid/uuid-user-models.md)

* Best Practices
    * [Roles vs Permissions](best-practices/roles-vs-permissions.md)
    * [Model Policies](best-practices/model-policies.md)

* Miscellaneous
    - [Testing](miscellaneous.md#testing)
    - [Questions and issues](miscellaneous.md#questions-and-issues)
    - [Changelog](miscellaneous.md#changelog)
    - [Contributing](miscellaneous.md#contributing)
    - [Security](miscellaneous.md#security)
    - [Credits](miscellaneous.md#credits)
    - [License](miscellaneous.md#license)