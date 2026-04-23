<?php

namespace Middlewares;

use Src\Request;
use Src\View;
use Model\Token;
use Model\User;

class TokenMiddleware
{
    public function handle(Request $request): Request
    {
        $authHeader = $request->headers['Authorization'] ?? '';

        if (empty($authHeader) || stripos($authHeader, 'Bearer ') !== 0 || strlen($authHeader) <= 7) {
            (new View())->toJSON(['error' => 'Token not provided'], 401);
            exit();
        }

        $token = trim(substr($authHeader, 7));

        $user_token = Token::where('token', $token)->first();

        if (!$user_token) {
            (new View())->toJSON(['error' => 'Invalid token'], 401);
            exit();
        }

        $user = User::find($user_token->user_id);

        if (!$user) {
            (new View())->toJSON(['error' => 'User not found'], 401);
            exit;
        }

        $request->set('user_id', $user->user_id);
        $request->set('api_user', $user);

        return $request;
    }
}