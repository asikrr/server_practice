<?php

namespace Controller;

use Model\Post;
use Src\View;
use Src\Request;

class Site
{
    public function index(Request $request): string
    {
        if ($request->id) {
            $posts = Post::where('id', $request->id)->get();
        } else {
            $posts = Post::all();
        }
        return (new View())->render('site.post', ['posts' => $posts]);
    }

    public function hello(): string
    {
        return new View('site.hello', ['message' => 'hello working']);
    }
}