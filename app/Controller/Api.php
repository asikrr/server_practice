<?php

namespace Controller;

use Model\Post;
use Model\User;
use Model\Resident;
use Src\Auth\Auth;
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
        $login = $request->all()['login'] ?? '';
        $password = $request->all()['password'] ?? '';

        if (empty($login) || empty($password)) {
            (new View())->toJSON(['error' => 'Login and password required'], 400);
            return;
        }

        $user = (new User())->attemptIdentity(['login' => $login, 'password' => $password]);
        if (!$user) {
            (new View())->toJSON(['error' => 'Invalid credentials'], 401);
            return;
        }

        $token = $user->createApiToken();
        (new View())->toJSON([
            'success' => true,
            'token' => $token,
            'user' => ['id' => $user->user_id, 'login' => $user->login]
        ]);
    }

    public function debtors(Request $request): void
    {
        $user = $this->authorize($request);
        if (!$user) return; 

        $debtors = ($user->role_id == 1) ? Resident::get_debtors(0) : Resident::get_debtors($user->getId()); 

        (new View())->toJSON([
            'debtors' => $debtors
        ]);
    }

    private function authorize(Request $request): ?User
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (stripos($authHeader, 'Bearer ') !== 0 || strlen($authHeader) <= 7) {
            (new View())->toJSON(['error' => 'Unauthorized: Bearer token required'], 401);
            return null;
        }

        $token = trim(substr($authHeader, 7));

        $tokenData = app()->db->table('api_tokens')->where('token', $token)->first();

        if (!$tokenData) {
            (new View())->toJSON(['error' => 'Unauthorized: Invalid token'], 401);
            return null;
        }

        $user = (new User())->findIdentity($tokenData->user_id);
        if (!$user) {
            (new View())->toJSON(['error' => 'Unauthorized: User not found'], 401);
            return null;
        }

        Auth::login($user);
        return $user;
    }

    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }
}