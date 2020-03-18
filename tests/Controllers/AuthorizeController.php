<?php

namespace Oslllo\Larakey\Test\Controllers;

use Exception;
use Illuminate\Http\Request;
use Oslllo\Larakey\Test\Models\Post;

class AuthorizeController extends Controller
{
    public function viewAnything()
    {
        $this->authorize('view');

        $this->authorize('view', '*');

        return response(200);
    }

    public function viewAnyPost()
    {
        $this->authorize('view', Post::class);

        return response(200);
    }

    public function viewPost($postId)
    {
        $post = Post::where('id', $postId)->first();

        $this->authorize('view', $post);

        return response(200);
    }
}