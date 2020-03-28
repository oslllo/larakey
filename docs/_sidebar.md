
#  

* Home
    * [Home](/)
    * [Introduction](introduction.md)
    * [Features](introduction.md#features)

* Installation
    * [⚠️ Prerequisites](installation/prerequisites.md)
    * [Installation](installation/installation.md)
    * [Default config file contents](installation/default-config-file-contents.md)

* Basic Usage
    * [Basic Usage](basic-usage/basic-usage.md#basic-usage)
    * [Eloquent](basic-usage/eloquent.md)

    * Using Permissions
        * [⚠️ Using Permissions](basic-usage/using-permissions/using-permissions.md)

        * Assigning Permissions
            * [Assigning Permissions](basic-usage/using-permissions/assigning-permissions/assigning-permissions.md#assigning-permissions)
            * [Give User Permission (to all classes and model instances)](basic-usage/using-permissions/assigning-permissions/assigning-permissions.md#give-permission)
            * [Give User Permission To A Class](basic-usage/using-permissions/assigning-permissions/assigning-permissions.md#give-permission-to-a-class)
            * [Give User Permission To A Model Instance](basic-usage/using-permissions/assigning-permissions/assigning-permissions.md#give-permission-to-a-model-instance)
            * [Give User Multiple Permissions To Something](basic-usage/using-permissions/assigning-permissions/assigning-permissions.md#give-multiple-permissions-to-something)
            * [Sync Permissions](basic-usage/using-permissions/assigning-permissions/sync-permissions.md)

        * Revoking Permissions
            * [⚠️ Revoking Permissions](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md)
                * [Revoke User Permission (to all classes and model instances)](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md#revoke-permissions)
                * [Revoke User Permission To A Class](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md#revoke-permission-to-class)
                * [Revoke User Permission To A Model Instance](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md#revoke-permission-to-instance)
                * [Revoke User Multiple Permissions To Something](basic-usage/using-permissions/revoking-permissions/revoking-permissions.md#revoke-multiple-permissions-to-something)
                * [〽️ Revoke With Recursion](basic-usage/using-permissions/revoking-permissions/with-recursion.md)

        * Checking For Permissions
            * [Checking For Permissions](basic-usage/using-permissions/checking-for-permissions/checking-for-permissions.md)
            * [Checking For Direct Permissions](basic-usage/using-permissions/checking-for-permissions/checking-for-direct-permissions.md)
            * [Checking For Any Permissions](basic-usage/using-permissions/checking-for-permissions/checking-for-any-permissions.md)
            * [Checking For All Permissions](basic-usage/using-permissions/checking-for-permissions/checking-for-all-permissions.md)
            * [Checking Permissions Via Roles](basic-usage/using-permissions/checking-for-permissions/checking-permissions-via-roles.md)

        * Getting Permissions
            * [Get Direct Permissions](basic-usage/using-permissions/getting-permissions/get-direct-permissions.md)
            * [Get All Permissions](basic-usage/using-permissions/getting-permissions/get-all-permissions.md)
            * [Get Permission Role](basic-usage/using-permissions/getting-permissions/get-permission-role.md)
            * [Get Permissions Via Role](basic-usage/using-permissions/getting-permissions/get-permissions-via-rolea.md)

    * Using Roles
        * [Using Roles](basic-usage/using-roles/using-roles.md)
        * [Using permissions with roles](basic-usage/using-roles/using-permissions-with-roles.md)

        * Assigning Roles
            * [Assigning Roles](basic-usage/using-roles/assigning-roles.md)
            * [Syncing Roles](basic-usage/using-roles/syncing-roles.md)

        * Revoking Roles
            * [Revoking Roles](basic-usage/using-roles/revoking-roles.md)

        * Checking For Roles
            * [Checking For Roles](basic-usage/using-roles/checking-for-roles.md)
            * [Checking For Any Role](basic-usage/using-roles/checking-for-any-role.md)
            * [Checking For All Roles](basic-usage/using-roles/checking-for-all-roles.md)

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

* Advanced Usage
    * [Unit Testing](advanced-usage/unit-testing.md)
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
        * [Manual Cache Reset](advanced-usage/cache/manual-cache-reset.md)
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
    * [Testing](miscellaneous.md#testing)
    * [Questions and issues](miscellaneous.md#questions-and-issues)
    * [Changelog](miscellaneous.md#changelog)
    * [Contributing](miscellaneous.md#contributing)
    * [Security](miscellaneous.md#security)
    * [Credits](miscellaneous.md#credits)
    * [License](miscellaneous.md#license)
