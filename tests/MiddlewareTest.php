<?php

namespace Ghustavh97\Larakey\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Ghustavh97\Larakey\Middlewares\RoleMiddleware;
use Ghustavh97\Larakey\Exceptions\UnauthorizedException;
use Ghustavh97\Larakey\Middlewares\PermissionMiddleware;
use Ghustavh97\Larakey\Middlewares\RoleOrPermissionMiddleware;

class MiddlewareTest extends TestCase
{
    protected $roleMiddleware;
    protected $permissionMiddleware;
    protected $roleOrPermissionMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleMiddleware = new RoleMiddleware();

        $this->permissionMiddleware = new PermissionMiddleware();

        $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware();
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_role_or_permission_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->roleOrPermissionMiddleware, 'testUserRole'
            ), 403);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_rolemiddleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testUserRole'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_role_middleware_if_have_this_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testUserRole');

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testUserRole'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_role_middleware_if_have_one_of_the_roles()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testUserRole');

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testUserRole|testUserRole2'
            ), 200);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, ['testUserRole2', 'testUserRole']
            ), 200);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_role_middleware_if_have_a_different_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole(['testUserRole']);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testUserRole2'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_have_not_roles()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testUserRole|testUserRole2'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_role_is_undefined()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, ''
            ), 403);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-news|edit-articles'
            ), 200);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, ['edit-news', 'edit-articles']
            ), 200);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-news'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles|edit-news'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_role_middleware_if_has_this_permission_or_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testUserRole');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testUserRole|edit-news|edit-articles'),
            200
        );

        $this->testUser->removeRole('testUserRole');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testUserRole|edit-articles'),
            200
        );

        $this->testUser->revokePermissionTo('edit-articles');
        $this->testUser->assignRole('testUserRole');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testUserRole|edit-articles'),
            200
        );

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, ['testUserRole', 'edit-articles']),
            200
        );
    }

    /** @test */
    public function a_user_can_not_access_a_route_protected_by_permission_or_role_middleware_if_have_not_this_permission_and_role()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testUserRole|edit-articles'),
            403
        );

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'missingRole|missingPermission'),
            403
        );
    }

    /** @test */
    public function the_required_roles_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $requiredRoles = [];

        try {
            $this->roleMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-role');
        } catch (UnauthorizedException $e) {
            $requiredRoles = $e->getRequiredRoles();
        }

        $this->assertEquals(['some-role'], $requiredRoles);
    }

    /** @test */
    public function the_required_permissions_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $requiredPermissions = [];

        try {
            $this->permissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-permission');
        } catch (UnauthorizedException $e) {
            $requiredPermissions = $e->getRequiredPermissions();
        }

        $this->assertEquals(['some-permission'], $requiredPermissions);
    }

    protected function runMiddleware($middleware, $parameter)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $parameter)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
