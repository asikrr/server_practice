<?php
namespace Middlewares;

use Src\Request;
use Src\Session;

class AdminMiddleware
{
    public function handle(Request $request)
    {
        if (Session::get('role_id') != 1) {
            app()->route->redirect('/');
        }
    }
}