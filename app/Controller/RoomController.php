<?php

namespace Controller;

use Model\Dormitory;
use Model\Room;
use Model\RoomType;
use Src\Request;
use Src\View;
use Validator\Validator;

class RoomController {
    public function rooms(Request $request): string
    {
        $user = app()->auth->user();
        $is_admin = $user->role_id == 1;

        $rooms = Room::get_filtered(
            $user->getId(),
            $is_admin,
            $request->type_filter,
            $request->availability_filter
        );

        $types = RoomType::all();
        $dormitories = Dormitory::all();

        return (new View())->render('site.rooms', [
            'rooms' => $rooms,
            'types' => $types,
            'dormitories' => $dormitories,
            'request' => $request,
            'is_admin' => $is_admin,
        ]);
    }

    public function room_create(int $dormitory_id, Request $request): string
    {
        $types = RoomType::all();
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'room_number' => ['required', 'unique_room:' . $dormitory_id, 'max_length:10'],
                'floor' => ['required', 'max_length:10'],
                'capacity' => ['required', 'is_numeric', 'positive_number'],
                'type_id' => ['required']
            ], ['required' => 'Поле :field пусто',
                'unique_room' => 'Комната с таким номером уже существует в общежитии',
                'is_numeric' => 'Поле :field должно быть числом',
                'positive_number' => 'Поле :field не должно быть <= 0',
                'max_length' => 'Поле :field превышает лимит символов'],
                app()->settings->app['validators']);

            if ($validator->fails()) {
                return (new View())->render('site.room_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'room' => (object) $request->all(),
                    'dormitory_id' => $dormitory_id,
                    'page_title' => 'Добавление комнаты',
                    'types' => $types
                ]);
            }

            $data = $request->all();
            $data['dormitory_id'] = $dormitory_id;
            Room::create($data);
            app()->route->redirect('/dormitories');
        }

        return (new View())->render('site.room_form', [
            'room' => null,
            'dormitory_id' => $dormitory_id,
            'page_title' => 'Добавление комнаты',
            'types' => $types
        ]);
    }
}