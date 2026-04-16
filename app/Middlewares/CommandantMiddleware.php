<?php
namespace Middlewares;

use Src\Request;
use Src\Session;

class CommandantMiddleware
{
    public function handle(Request $request)
    {
        if (Session::get('role_id') != 2) {
            app()->route->redirect('/');
        }
    }
}