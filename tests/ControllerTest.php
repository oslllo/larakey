<?php

namespace Ghustavh97\Guardian\Test;

use Illuminate\Support\Facades\Auth;
use Ghustavh97\Guardian\Test\Models\Post;

class ControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function a_guest_cannot_access_controller_protected_by_authorization_permission()
    {
        $response = $this->canViewAnything();
        $response->assertStatus(403);

        $response = $this->canViewAnyPost();
        $response->assertStatus(403);

        $response = $this->canViewPost();
        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_can_access_controller_protected_by_authorization_if_has_this_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('view');

        $response = $this->canViewAnything();
        $response->assertStatus(200);

        $response = $this->canViewAnyPost();
        $response->assertStatus(200);

        $response = $this->canViewPost();
        $response->assertStatus(200);

        $this->testUser->revokePermissionTo('view');

        $this->assertFalse($this->testUser->hasPermissionTo('view'));

        $this->testUser->givePermissionTo('view', '*');

        $response = $this->canViewAnything();
        $response->assertStatus(200);

        $response = $this->canViewAnyPost();
        $response->assertStatus(200);

        $response = $this->canViewPost();
        $response->assertStatus(200);
    }

    /** @test */
    public function a_user_can_access_controller_protected_by_authorization_if_has_this_permission_to_class()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('view', Post::class);

        $response = $this->canViewAnything();
        $response->assertStatus(403);

        $response = $this->canViewAnyPost();
        $response->assertStatus(200);

        $response = $this->canViewPost();
        $response->assertStatus(200);
    }

    /** @test */
    public function a_user_can_access_controller_protected_by_authorization_if_has_this_permission_to_model_instance()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('view', $this->testUserPost);

        $response = $this->canViewAnything();
        $response->assertStatus(403);

        $response = $this->canViewAnyPost();
        $response->assertStatus(403);

        $response = $this->canViewPost();
        $response->assertStatus(200);
    }

    protected function canViewAnything()
    {
        return $this->get(route('authorization.view-anything'));
    }

    protected function canViewAnyPost()
    {
        return $this->get(route('authorization.view-any-post'));
    }

    protected function canViewPost()
    {
        return $this->get(route('authorization.view-post', ['post_id' => $this->testUserPost->id]));
    }
}