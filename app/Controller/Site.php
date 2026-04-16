<?php

namespace Controller;

use Model\Post;
use Model\User;
use Src\View;
use Src\Request;
use Src\Auth\Auth;
use Src\Validator\Validator;

class Site
{
    public function index(Request $request): string
    {
        return (new View())->render('site.index');
    }

    public function login(Request $request): string
    {
        if ($request->method === 'GET') {
            return new View('site.login');
        }

        if (Auth::attempt($request->all())) {
            app()->route->redirect('/');
        }

        return new View('site.login', ['message' => 'Неправильные логин или пароль']);
    }

    public function logout(): void
    {
        Auth::logout();
        app()->route->redirect('/login');
    }

    public function commandants(Request $request): string
    {
        $commandants = User::where('role_id', 2)->get();
        return (new View())->render('site.commandants', ['commandants' => $commandants]);
    }

    public function commandant_form(Request $request): string
    {
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'login' => ['required', 'unique:users,login'],
                'full_name' => ['required'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if($validator->fails()){
                return new View('site.commandant_form',
                    ['message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)]);
            }

            $data = $request->all();
            $data['role_id'] = 2;
            User::create($data);
            app()->route->redirect('/commandants');
        }
        return (new View())->render('site.commandant_form');
    }

    public function debtors(Request $request): string
    {
        return (new View())->render('site.debtors');
    }

    public function dormitories(Request $request): string
    {
        return (new View())->render('site.dormitories');
    }

    public function dormitory_form(Request $request): string
    {
        return (new View())->render('site.dormitory_form');
    }

    public function residents(Request $request): string
    {
        return (new View())->render('site.residents');
    }

    public function resident_form(Request $request): string
    {
        return (new View())->render('site.resident_form');
    }

    public function rooms(Request $request): string
    {
        return (new View())->render('site.rooms');
    }

    public function room_form(Request $request): string
    {
        return (new View())->render('site.room_form');
    }

    // public function index(Request $request): string
    // {
    //     if ($request->id) {
    //         $posts = Post::where('id', $request->id)->get();
    //     } else {
    //         $posts = Post::all();
    //     }
    //     return (new View())->render('site.post', ['posts' => $posts]);
    // }

    // public function hello(): string
    // {
    //     return new View('site.hello', ['message' => 'hello working']);
    // }

    // public function signup(Request $request): string
    // {
    //     if ($request->method === 'POST') {

    //         $validator = new Validator($request->all(), [
    //             'name' => ['required'],
    //             'login' => ['required', 'unique:users,login'],
    //             'password' => ['required']
    //         ], [
    //             'required' => 'Поле :field пусто',
    //             'unique' => 'Поле :field должно быть уникально'
    //         ]);

    //         if($validator->fails()){
    //             return new View('site.signup',
    //                 ['message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)]);
    //         }

    //         if (User::create($request->all())) {
    //             app()->route->redirect('/login');
    //         }
    //     }
    //     return new View('site.signup');
    // }
}