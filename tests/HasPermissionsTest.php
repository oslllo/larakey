<?php

namespace Oslllo\Larakey\Test;

use Oslllo\Larakey\Test\TestCase;
use Oslllo\Larakey\Contracts\Role;
use Oslllo\Larakey\Padlock\Config;
use Oslllo\Larakey\Test\App\Models\User;
use Oslllo\Larakey\Test\App\Models\Post;
use Oslllo\Larakey\Contracts\Permission;
use Oslllo\Larakey\Exceptions\GuardDoesNotMatch;
use Oslllo\Larakey\Exceptions\ClassDoesNotExist;
use Oslllo\Larakey\Exceptions\PermissionDoesNotExist;
use Oslllo\Larakey\Exceptions\PermissionNotAssigned;
use Oslllo\Larakey\Exceptions\StrictPermission;
use Oslllo\Larakey\Exceptions\InvalidArguments;
use Oslllo\Larakey\Test\App\Models\SoftDeletingUser;

class HasPermissionsTest extends TestCase
{
    /** @test */
    public function user_only_has_permission_assigned_to_them()
    {
        $this->testUser->givePermissionTo('view');

        $this->assertTrue($this->testUser->hasPermissionTo('view'));

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', '*'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', $this->testUserPost));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class, $this->testUserPost->id));
    }

    /** @test */
    public function it_can_assign_a_permission_to_a_user()
    {
        $this->testUser->givePermissionTo('view');

        $this->assertTrue($this->testUser->hasPermissionTo('view'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', '*'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo('view', $this->testUserPost));
        $this->assertTrue($this->testUser->hasPermissionTo('view', Post::class, $this->testUserPost->id));

        $this->assertTrue($this->testUser->hasPermissionTo('view', 'web'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', '*', 'web'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', Post::class, 'web'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', $this->testUserPost, 'web'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', Post::class, $this->testUserPost->id, 'web'));
    }

    /** @test */
    public function it_can_assign_a_permission_to_a_user_with_wildcard_token()
    {
        $this->testUser->givePermissionTo('view', '*');

        $this->assertTrue($this->testUser->hasPermissionTo('view'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', '*'));
        $this->assertTrue($this->testUser->hasPermissionTo('view', Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo('view', $this->testUserPost));
        $this->assertTrue($this->testUser->hasPermissionTo('view', Post::class, $this->testUserPost->id));
    }

    /** @test */
    public function it_can_assign_permission_with_class_to_a_user()
    {
        $this->testUser->givePermissionTo('manage', Post::class);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', '*'));

        $this->assertTrue($this->testUser->hasPermissionTo('manage', Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', $this->testUserPost));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', Post::class, $this->testUserPost->id));
    }

    /** @test */
    public function it_can_assign_permission_with_model_instance_to_a_user()
    {
        $this->testUser->givePermissionTo('manage', $this->testUserPost);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', '*'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class));

        $this->assertTrue($this->testUser->hasPermissionTo('manage', $this->testUserPost));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', Post::class, $this->testUserPost->id));
    }

    /** @test */
    public function it_can_assign_permission_with_model_class_and_id_to_a_user()
    {
        $this->testUser->givePermissionTo('manage', Post::class, $this->testUserPost->id);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', '*'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class));

        $this->assertTrue($this->testUser->hasPermissionTo('manage', $this->testUserPost));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', Post::class, $this->testUserPost->id));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_permission_with_a_class_that_does_not_exist()
    {
        $this->expectException(ClassDoesNotExist::class);

        $this->testUser->givePermissionTo('manage', '\Class\That\Does\Not\Exist');

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', '\Class\That\Does\Not\Exist'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->givePermissionTo('permission-does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_permission_to_a_user_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->givePermissionTo($this->testAdminPermission);

        $this->expectException(GuardDoesNotMatch::class);
        
        $this->testUser->givePermissionTo($this->testAdminPermission, Post::class);

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->givePermissionTo('admin-permission');
    }

    /** @test */
    public function it_throws_an_exception_when_strict_permission_assigment_true_and_no_permission_scope_is_provided()
    {
        app('config')->set('larakey.strict.permission.assignment', true);

        $this->expectException(StrictPermission::class);

        $this->testUser->givePermissionTo('view');
    }

    /** @test */
    public function does_not_throw_an_exception_when_strict_permission_assigment_false_and_no_permission_scope_is_provided()
    {
        app('config')->set('larakey.strict.permission.assignment', false);

        $this->testUser->givePermissionTo('view');

        $this->assertTrue($this->testUser->hasPermissionTo('view'));
    }

    /** @test */
    public function does_not_throw_an_exception_when_strict_permission_assigment_true_and_permission_scope_is_provided()
    {
        app('config')->set('larakey.strict.permission.assignment', false);

        $this->testUser->givePermissionTo('view', '*');

        $this->assertTrue($this->testUser->hasPermissionTo('view', '*'));
    }

    /** @test */
    public function it_throws_an_exception_when_too_many_arguments_are_passed()
    {
        $this->expectException(InvalidArguments::class);

        $this->testUser->hasPermissionTo('manage', Post::class, $this->testUserPost->id, 'api', true, true);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
    }

    /** @test */
    public function it_throws_an_exception_when_strict_permission_revoke_true_and_no_permission_scope_is_provided()
    {
        app('config')->set('larakey.strict.permission.revoke', true);

        $this->expectException(StrictPermission::class);

        $this->testUser->revokePermissionTo('view');
    }

    /** @test */
    public function does_not_throw_an_exception_when_strict_permission_revoke_true_and_permission_scope_is_provided()
    {
        app('config')->set('larakey.strict.permission.revoke', true);

        $this->testUser->givePermissionTo('view', '*');

        $this->testUser->revokePermissionTo('view', '*');

        $this->assertFalse($this->testUser->hasPermissionTo('view'));
    }

    /** @test */
    public function does_not_throw_an_exception_when_strict_permission_revoke_false_and_permission_scope_is_not_provided()
    {
        app('config')->set('larakey.strict.permission.revoke', false);

        $this->testUser->givePermissionTo('view');

        $this->testUser->revokePermissionTo('view');

        $this->assertFalse($this->testUser->hasPermissionTo('view'));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user_using_recursion_if_set_to_true()
    {
        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission],
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission, true);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user_using_recursion_if_set_to_true_in_config()
    {
        $this->app['config']->set(Config::$recursionOnPermissionRevoke, true);

        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission],
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user_and_not_use_recursion_if_set_to_false()
    {
        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission],
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission, false);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user_and_not_use_recursion_if_set_to_false_in_config()
    {
        $this->app['config']->set(Config::$recursionOnPermissionRevoke, false);

        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission],
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user_and_not_user_recursion_if_set_to_false()
    {
        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission],
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission, false);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_with_class_from_a_user_with_recursion_set_to_true()
    {
        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
        
        $this->testUser->revokePermissionTo($this->testUserPermission, Post::class, true);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_with_class_from_a_user_with_recursion_set_to_false()
    {
        $this->testUser->giveMultiplePermissionsTo([
            [$this->testUserPermission, Post::class],
            [$this->testUserPermission, $this->testUserPost]
        ]);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
        
        $this->testUser->revokePermissionTo($this->testUserPermission, Post::class, false);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_with_model_instance_from_a_user_with_recursion_set_to_true()
    {
        $this->testUser->givePermissionTo($this->testUserPermission, $this->testUserPost);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
        
        $this->testUser->revokePermissionTo($this->testUserPermission, $this->testUserPost, true);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_with_class_from_a_user()
    {
        $this->testUser->givePermissionTo('manage', Post::class);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', $this->testUserPost));

        $this->testUser->revokePermissionTo('manage', Post::class);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', $this->testUserPost));
    }

    /** @test */
    public function it_can_revoke_a_permission_with_model_from_a_user()
    {
        $this->testUser->givePermissionTo('manage', $this->testUserPost);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class));
        $this->assertTrue($this->testUser->hasPermissionTo('manage', $this->testUserPost));

        $this->testUser->revokePermissionTo('manage', $this->testUserPost);

        $this->assertFalse($this->testUser->hasPermissionTo('manage'));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', Post::class));
        $this->assertFalse($this->testUser->hasPermissionTo('manage', $this->testUserPost));
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testUserRole');

        $scopedUsers1 = User::permission('edit-articles')->get();
        $scopedUsers2 = User::permission(['edit-news'])->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_a_string_with_class()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        $user1->giveMultiplePermissionsTo([
            [['edit-articles', 'edit-news'], Post::class],
            ['edit-articles', $this->testUser],
        ]);

        $this->testUserRole->givePermissionTo('edit-articles', Post::class);
        
        $user2->assignRole('testUserRole');

        $scopedUsers1 = User::permission('edit-articles', Post::class)->get();
        $scopedUsers2 = User::permission(['edit-news'], Post::class)->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_a_string_with_model_instance()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news'], $this->testUserPost);
        $this->testUserRole->givePermissionTo('edit-articles', $this->testUserPost);
        $user2->assignRole('testUserRole');

        $scopedUsers1 = User::permission('edit-articles', $this->testUserPost)->get();
        $scopedUsers2 = User::permission(['edit-news'], $this->testUserPost)->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testUserRole');

        $scopedUsers1 = User::permission(['edit-articles', 'edit-news'])->get();
        $scopedUsers2 = User::permission(['edit-news'])->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testUserRole');

        $scopedUsers1 = User::permission(collect(['edit-articles', 'edit-news']))->get();
        $scopedUsers2 = User::permission(collect(['edit-news']))->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user1->givePermissionTo($this->testUserPermission->name);

        $scopedUsers1 = User::permission($this->testUserPermission)->get();
        $scopedUsers2 = User::permission([$this->testUserPermission])->get();
        $scopedUsers3 = User::permission(collect([$this->testUserPermission]))->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 1);
        $this->assertEquals($scopedUsers3->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_without_permissions_only_role()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user1->assignRole('testUserRole');
        $user2->assignRole('testUserRole');

        $scopedUsers = User::permission('edit-articles')->get();

        $this->assertEquals($scopedUsers->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_without_permissions_only_permission()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo(['edit-news']);
        $user2->givePermissionTo(['edit-articles', 'edit-news']);

        $scopedUsers = User::permission('edit-news')->get();

        $this->assertEquals($scopedUsers->count(), 2);
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_an_invalid_type()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasPermissionTo(new \stdClass());
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_null()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasPermissionTo(null);
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasDirectPermission_with_an_invalid_type()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasDirectPermission(new \stdClass());
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasDirectPermission_with_null()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasDirectPermission(null);
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_non_existing_permission()
    {
        $this->expectException(PermissionDoesNotExist::class);

        User::permission('not defined permission')->get();
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_permission_from_another_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        User::permission('testAdminPermission')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::permission($this->testAdminPermission)->get();
    }

    /** @test */
    public function it_doesnt_detach_permissions_when_soft_deleting()
    {
        $user = SoftDeletingUser::create(['email' => 'test@example.com']);
        $user->givePermissionTo(['edit-news']);
        $user->delete();

        $user = SoftDeletingUser::withTrashed()->find($user->id);

        $this->assertTrue($user->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_give_and_revoke_multiple_permissions()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->assertTrue($this->testUserRole->hasPermissionTo(['edit-articles', 'edit-news']));

        $this->testUserRole->revokePermissionTo(['edit-articles', 'edit-news']);

        $this->assertFalse($this->testUserRole->hasPermissionTo(['edit-articles', 'edit-news']));

        $this->assertEquals(0, $this->testUserRole->permissions()->count());


        $this->testUserRole->givePermissionTo(['view', 'edit'], Post::class);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->assertFalse($this->testUserRole->hasPermissionTo(['view', 'edit']));
        $this->assertTrue($this->testUserRole->hasPermissionTo(['view', 'edit'], Post::class));

        $this->testUserRole->revokePermissionTo(['view', 'edit'], Post::class);

        $this->assertFalse($this->testUserRole->hasPermissionTo(['view', 'edit'], Post::class));

        $this->assertEquals(0, $this->testUserRole->permissions()->count());


        $this->testUser->giveMultiplePermissionsTo([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasAllPermissions([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]));


        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news'], Post::class);
        
        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-news', '*'));
        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-news', Post::class));
        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles', Post::class));
        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles', $this->testUserPost));

        $this->testUserRole->revokePermissionTo(['edit-articles', 'edit-news'], Post::class);

        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-news', '*'));
        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-news', Post::class));
        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-articles', Post::class));
        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-articles', $this->testUserPost));

        $this->assertEquals(0, $this->testUserRole->permissions()->count());
    }

    /** @test */
    public function it_throws_an_exception_when_you_revoke_a_permission_the_user_doesnt_have()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->expectException(PermissionNotAssigned::class);

        $this->testUserRole->revokePermissionTo(['edit-articles', 'edit-news'], Post::class);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist_for_this_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('admin-permission');
    }

    /** @test */
    public function it_can_work_with_a_user_that_does_not_have_any_permissions_at_all()
    {
        $user = new User();

        $this->assertFalse($user->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly()
    {
        $this->testUser->givePermissionTo('edit', $this->testUserPost);

        $this->assertTrue($this->testUser->hasAnyPermission([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]));

        $this->testUser->revokePermissionTo('edit', true);
        
        $this->testUser->givePermissionTo('create', User::class);

        $this->assertFalse($this->testUser->hasAnyPermission([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAnyPermission(['edit-articles']));

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));

        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testUserRole');

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_all_of_the_permissions_directly()
    {
        $this->testUser->giveMultiplePermissionsTo([
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost]
        ]);

        $this->assertTrue($this->testUser->hasAllPermissions([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]));

        $this->assertFalse($this->testUser->hasAllPermissions([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['delete', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]));

        $this->testUser->revokePermissionTo('edit', true);

        $this->assertFalse($this->testUser->hasAllPermissions([
            'view',
            ['view', '*'],
            ['create', Post::class],
            ['edit', Post::class, 1],
            ['delete', $this->testUserPost],
            [['view', 'create', 'edit', 'delete'], Post::class, 1],
            [['view', 'create', 'edit', 'delete'], $this->testUserPost]
        ]));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_all_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));

        $this->expectException(PermissionNotAssigned::class);

        $this->testUser->revokePermissionTo('edit-articles');

        $this->assertFalse($this->testUser->hasAllPermissions(['edit-news', 'edit-articles']));

        $this->testUser->givePermissionTo('edit-news');

        $this->expectException(PermissionNotAssigned::class);

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_all_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->testUser->assignRole('testUserRole');

        $this->assertTrue($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_user_has_direct_permission()
    {
        $this->testUser->givePermissionTo('edit', '*');
        $this->assertTrue($this->testUser->hasDirectPermission('edit'));
        $this->assertTrue($this->testUser->hasDirectPermission('edit', Post::class));
        $this->assertTrue($this->testUser->hasDirectPermission('edit', $this->testUserPost));

        $this->assertEquals(
            collect(['edit']),
            $this->testUser->getDirectPermissions()->pluck('name')
        );

        $this->testUser->revokePermissionTo('edit');
        $this->assertFalse($this->testUser->hasDirectPermission('edit'));
        $this->assertFalse($this->testUser->hasDirectPermission('edit', Post::class));
        $this->assertFalse($this->testUser->hasDirectPermission('edit', $this->testUserPost));

        $this->testUser->assignRole('testUserRole');
        $this->testUserRole->givePermissionTo('edit');
        $this->assertFalse($this->testUser->hasDirectPermission('edit'));
    }

    /** @test */
    public function it_can_determine_that_user_has_direct_permission_to_class()
    {
        $this->testUser->givePermissionTo('edit', Post::class);
        $this->assertTrue($this->testUser->hasDirectPermission('edit', Post::class));
        $this->assertTrue($this->testUser->hasDirectPermission('edit', $this->testUserPost));

        $this->assertFalse($this->testUser->hasDirectPermission('edit', '*'));

        $userDirectPermissions = $this->testUser->getDirectPermissions();

        $this->assertEquals(
            collect(['edit', Post::class]),
            $this->testUser->getDirectPermissions()->larakeyPluckMultiple(['name', 'to_type'])
        );

        $this->testUser->revokePermissionTo('edit', Post::class);
        $this->assertFalse($this->testUser->hasDirectPermission('edit', Post::class));

        $this->assertFalse($this->testUser->hasDirectPermission('edit', $this->testUserPost));
        $this->assertFalse($this->testUser->hasDirectPermission('edit', '*'));

        $this->testUser->assignRole('testUserRole');
        $this->testUserRole->givePermissionTo('edit', Post::class);
        $this->assertFalse($this->testUser->hasDirectPermission('edit', Post::class));
    }

    /** @test */
    public function it_can_determine_that_user_has_direct_permission_to_model_instance()
    {
        $this->testUser->givePermissionTo('edit', $this->testUserPost);
        $this->assertTrue($this->testUser->hasDirectPermission('edit', $this->testUserPost));

        $this->assertFalse($this->testUser->hasDirectPermission('edit', '*'));
        $this->assertFalse($this->testUser->hasDirectPermission('edit', Post::class));

        $userDirectPermissions = $this->testUser->getDirectPermissions();

        $this->assertEquals(
            collect(['edit', get_class($this->testUserPost), $this->testUserPost->id]),
            $this->testUser->getDirectPermissions()->larakeyPluckMultiple(['name', 'to_type', 'to_id'])
        );

        $this->testUser->revokePermissionTo('edit', $this->testUserPost);
        $this->assertFalse($this->testUser->hasDirectPermission('edit', $this->testUserPost));

        $this->assertFalse($this->testUser->hasDirectPermission('edit', '*'));
        $this->assertFalse($this->testUser->hasDirectPermission('edit', Post::class));

        $this->testUser->assignRole('testUserRole');
        $this->testUserRole->givePermissionTo('edit', $this->testUserPost);
        $this->assertFalse($this->testUser->hasDirectPermission('edit', $this->testUserPost));
    }

    /** @test */
    public function it_can_list_all_the_permissions_via_roles_of_user()
    {
        $roleModel = app(Role::class);
        $roleModel->findByName('testUserRole2')->givePermissionTo('edit-news');

        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testUserRole', 'testUserRole2');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionsViaRoles()->pluck('name')
        );
    }

    /** @test */
    public function it_can_determine_that_user_has_permission_via_multiple_roles()
    {
        $roleModel = app(Role::class);
        $roleWithPermissions = $roleModel->create(['name' => 'withPermissions']);
        $rolewithoutPermissions = $roleModel->create(['name' => 'withoutPermissions']);
        $roleWithPermissions->givePermissionTo('edit-articles');

        $newUser = new User;
        $newUser->email = 'someuser@app.com';
        $newUser->save();
        
        $newUser->assignRole($rolewithoutPermissions);

        $newUser->email = 'someuser@app.com2';
        $newUser->save();

        $newUser->assignRole($this->testUserRole, 'withPermissions');

        $this->assertEquals(3, $newUser->roles->count());

        $this->assertTrue($newUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_list_all_the_coupled_permissions_both_directly_and_via_roles()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testUserRole');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getAllPermissions()->pluck('name')->sort()->values()
        );
    }

    /** @test */
    public function it_can_sync_multiple_permissions()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->syncPermissions(['edit-articles', 'edit-blog']);

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));

        $this->assertFalse($this->testUser->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_can_sync_multiple_permissions_by_id()
    {
        $this->testUser->givePermissionTo('edit-news');

        $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-blog'])->pluck('id');

        $this->testUser->syncPermissions($ids->toArray());

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));

        $this->assertFalse($this->testUser->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function sync_permission_ignores_null_inputs()
    {
        $this->testUser->givePermissionTo('edit-news');

        $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-blog'])->pluck('id');

        $ids->push(null);

        $this->testUser->syncPermissions($ids->toArray());

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));

        $this->assertFalse($this->testUser->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_does_not_remove_already_associated_permissions_when_assigning_new_permissions()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_assigning_a_permission_that_is_already_assigned()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->givePermissionTo('edit-news');

        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_can_sync_permissions_to_a_model_that_is_not_persisted()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncPermissions(['edit-articles']);
        $user->save();

        $this->assertTrue($user->hasPermissionTo('edit-articles'));

        $user->syncPermissions(['edit-articles']);
        $this->assertTrue($user->hasPermissionTo('edit-articles'));
        $this->assertTrue($user->fresh()->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function calling_givePermissionTo_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->givePermissionTo('edit-news');
        $user->save();

        $user2 = new User(['email' => 'test2@user.com']);
        $user2->givePermissionTo('edit-articles');
        $user2->save();

        $this->assertTrue($user2->fresh()->hasPermissionTo('edit-articles'));
        $this->assertFalse($user2->fresh()->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function calling_syncPermissions_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncPermissions(['edit-news']);
        $user->save();

        $user2 = new User(['email' => 'test2@user.com']);
        $user2->syncPermissions(['edit-articles']);
        $user2->save();

        $this->assertTrue($user2->fresh()->hasPermissionTo('edit-articles'));
        $this->assertFalse($user2->fresh()->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_retrieve_permission_names()
    {
        $this->testUser->givePermissionTo(['edit-news', 'edit-articles']);
        $this->assertEquals(
            collect(['edit-news', 'edit-articles']),
            $this->testUser->getPermissionNames()
        );
    }
}
