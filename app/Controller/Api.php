<?php

namespace Controller;

use Model\Post;
use Model\User;
use Model\Resident;
use Src\Request;
use Src\View;

class Api
{
    public function index(): void
    {
        (new View())->toJSON(Post::all()->toArray());
    }

    public function login(Request $request): void
    {
        $data = $request->all();
        $login = $data['login'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('login', $login)->first();

        if (!$user || md5($password) !== $user->password) {
            (new View())->toJSON(['error' => 'Invalid credentials'], 401);
            return;
        }

        $token = md5($user->user_id . time());

        \Model\Token::create([
            'user_id' => $user->user_id,
            'token' => $token
        ]);

        (new View())->toJSON([
            'success' => true,
            'token' => $token,
            'user' => ['id' => $user->user_id, 'login' => $user->login]
        ]);
    }

    public function debtors(Request $request): void
    {
        $user = $request->get('api_user');

        if (!$user) {
            (new View())->toJSON(['error' => 'Unauthorized'], 401);
            return;
        }

        $debtors = ($user->role_id == 1)
            ? Resident::get_debtors(0)
            : Resident::get_debtors($user->user_id);

        (new View())->toJSON([
            'success' => true,
            'count' => count($debtors),
            'debtors' => $debtors
        ]);
    }

    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }
}