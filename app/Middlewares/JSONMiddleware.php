<?php

namespace Middlewares;

use Exception;
use Src\Request;
use Src\Session;

class JSONMiddleware
{
    public function handle(Request $request): Request
    {
        if ($request->method === 'GET') {
            return $request;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];

        foreach ($data as $key => $item) {
            $request->set($key, $item);
        }

        return $request;
    }
}