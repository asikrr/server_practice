<?php

namespace Controller;

use Model\Dormitory;
use Model\Room;
use Model\User;
use Src\Request;
use Src\View;

class IndexController
{
    public function index(Request $request): string
    {
        $user = app()->auth->user();
        $data = [
            'user_name' => $user->full_name,
            'role_id'   => $user->role_id
        ];

        if ($user->role_id == 1) {
            $data['dormitories_count'] = Dormitory::count();
            $data['commandants_count'] = User::get_commandants_count();
        } elseif ($user->role_id == 2) {
            $dormitory = Dormitory::find_by_commandant($user->user_id);
            $data['dormitory'] = $dormitory;
            $data['rooms_count'] = $dormitory ? Room::get_count_by_dormitory($dormitory->dormitory_id) : 0;
        }

        return (new View())->render('site.index', $data);
    }
}