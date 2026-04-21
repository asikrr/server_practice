<?php

namespace Controller;

use Model\Dormitory;
use Model\User;
use Src\Request;
use Src\View;
use Validator\Validator;

class DormitoryController {
    public function dormitories(Request $request): string
    {
        return (new View())->render('site.dormitories', [
            'dormitories' => Dormitory::get_all_with_commandants(),
            'commandants' => User::get_commandants(),
            'request' => $request
        ]);
    }

    public function dormitory_create(Request $request): string
    {
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'dormitory_number' => ['required', 'unique:dormitories,dormitory_number'],
                'city' => ['required'],
                'street' => ['required'],
                'building' => ['required'],
                'price' => ['required', 'is_numeric', 'positive_number']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально',
                'is_numeric' => 'Поле :field должно быть числом',
                'positive_number' => 'Поле :field не должно быть <= 0'
            ],
                app()->settings->app['validators']);

            if ($validator->fails()) {
                return (new View())->render('site.dormitory_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'dormitory' => null,
                    'page_title' => 'Добавление общежития'
                ]);
            }

            Dormitory::create($request->all());
            app()->route->redirect('/dormitories');
        }
        return (new View())->render('site.dormitory_form', [
            'dormitory' => null,
            'page_title' => 'Добавление общежития'
        ]);
    }
}