<?php

namespace Oslllo\Larakey\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    /**
     * Required roles.
     *
     * @var array
     */
    private $requiredRoles = [];

    /**
     * Required permissions.
     *
     * @var array
     */
    private $requiredPermissions = [];

    /**
     * Exception function for when user does not have the right role.
     *
     * @param array $roles
     *
     * @return self
     */
    public static function forRoles(array $roles): self
    {
        $message = 'User does not have the right roles.';

        if (config('larakey.display_permission_in_exception')) {
            $permStr = implode(', ', $roles);
            $message = 'User does not have the right roles. Necessary roles are '.$permStr;
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredRoles = $roles;

        return $exception;
    }

    /**
     * Exception function for when user does not have the right permissions.
     *
     * @param array $permissions
     *
     * @return self
     */
    public static function forPermissions(array $permissions): self
    {
        $message = 'User does not have the right permissions.';

        if (config('larakey.display_permission_in_exception')) {
            $permStr = implode(', ', $permissions);
            $message = 'User does not have the right permissions. Necessary permissions are '.$permStr;
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $permissions;

        return $exception;
    }

    /**
     * Exception function for when user does not have the necessary access rights.
     *
     * @param array $rolesOrPermissions
     *
     * @return self
     */
    public static function forRolesOrPermissions(array $rolesOrPermissions): self
    {
        $message = 'User does not have any of the necessary access rights.';

        if (config('larakey.display_permission_in_exception') && config('larakey.display_role_in_exception')) {
            $permStr = implode(', ', $rolesOrPermissions);
            $message = 'User does not have the right permissions. Necessary permissions are '.$permStr;
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $rolesOrPermissions;

        return $exception;
    }

    /**
     * Exception function for when user is not logged in.
     *
     * @return self
     */
    public static function notLoggedIn(): self
    {
        return new static(403, 'User is not logged in.', null, []);
    }

    /**
     * Gets required roles.
     *
     * @return array
     */
    public function getRequiredRoles(): array
    {
        return $this->requiredRoles;
    }

    /**
     * Gets required permissions.
     *
     * @return array
     */
    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }
}
