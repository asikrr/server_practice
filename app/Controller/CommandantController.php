<?php

namespace Controller;

use Model\Dormitory;
use Model\User;
use Src\Request;
use Src\View;
use Validator\Validator;

class CommandantController {
    public function commandants(Request $request): string
    {
        $commandants = User::get_commandants();
        $dormitories = Dormitory::get_commandant_dormitories();

        return (new View())->render('site.commandants', [
            'commandants' => $commandants,
            'dormitories' => $dormitories
        ]);
    }

    public function commandant_create(Request $request): string
    {
        $free_dorms = Dormitory::get_free_dormitories();

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'login' => ['required', 'unique:users,login', 'max_length:255'],
                'full_name' => ['required', 'max_length:255'],
                'password' => ['required', 'max_length:255']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально',
                'max_length' => 'Поле :field превышает лимит символов'
            ],
                app()->settings->app['validators']);

            if ($validator->fails()) {
                return (new View())->render('site.commandant_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'free_dorms' => $free_dorms,
                    'commandant' => (object) $request->all(),
                    'page_title' => 'Добавление коменданта'
                ]);
            }

            $data = $request->all();
            $data['role_id'] = 2;

            $commandant = User::create($data);
            Dormitory::assign_dormitory_to_commandant($commandant->user_id, $data['dormitory_id'] ?? null);

            app()->route->redirect('/commandants');
            return '';
        }

        return (new View())->render('site.commandant_form', [
            'free_dorms' => $free_dorms,
            'commandant' => null,
            'page_title' => 'Добавление коменданта'
        ]);
    }

    public function commandant_update(int $id, Request $request): string
    {
        $commandant = User::find_by_id($id);

        if (!$commandant) {
            app()->route->redirect('/commandants');
        }

        $free_dorms = Dormitory::get_available_for_commandant($commandant->user_id);

        if ($request->method === 'POST') {
            $new_dorm_id = $request->dormitory_id !== '' ? (int)$request->dormitory_id : null;
            Dormitory::assign_dormitory_to_commandant($commandant->user_id, $new_dorm_id);

            app()->route->redirect('/commandants');
        }

        $current_dorm_id = Dormitory::find_by_commandant($commandant->user_id)?->dormitory_id;

        return (new View())->render('site.commandant_form', [
            'commandant' => $commandant,
            'free_dorms' => $free_dorms,
            'current_dorm_id' => $current_dorm_id, 
            'page_title' => 'Редактирование коменданта'
        ]);
    }
}