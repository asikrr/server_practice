<?php

namespace Controller;

use Model\Post;
use Model\User;
use Model\Dormitory;
use Model\Room;
use Model\RoomType;
use Src\View;
use Src\Request;
use Src\Auth\Auth;
use Src\Validator\Validator;

class Site
{
    public function index(Request $request): string
    {
        return (new View())->render('site.index');
    }

    public function login(Request $request): string
    {
        if ($request->method === 'GET') {
            return new View('site.login');
        }

        if (Auth::attempt($request->all())) {
            app()->route->redirect('/');
        }

        return new View('site.login', ['message' => 'Неправильные логин или пароль']);
    }

    public function logout(): void
    {
        Auth::logout();
        app()->route->redirect('/login');
    }

    public function commandants(Request $request): string
    {
        $commandants = User::where('role_id', 2)->get();
        $dormitories = Dormitory::whereIn('user_id', $commandants->pluck('user_id'))->get();
        $busyIds = $dormitories->pluck('user_id')->unique()->toArray();

        return (new View())->render('site.commandants', [
            'commandants' => $commandants,
            'dormitories' => $dormitories,
            'busyIds' => $busyIds 
        ]);
    }

    public function commandant_create(Request $request): string
    {
        $freeDorms = Dormitory::whereNull('user_id')->get();

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'login' => ['required', 'unique:users,login'],
                'full_name' => ['required'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if($validator->fails()){
                return new View('site.commandant_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'freeDorms' => $freeDorms,
                    'commandant' => null,
                    'pageTitle' => 'Добавление коменданта'
                ]);
            }

            $data = $request->all();
            $data['role_id'] = 2;
            
            $commandant = User::create($data);
            if (!empty($data['dormitory_id'])) {
                Dormitory::where('dormitory_id', $data['dormitory_id'])
                    ->update(['user_id' => $commandant->user_id]);
            }
            
            app()->route->redirect('/commandants');
        }
        
        return (new View())->render('site.commandant_form', [
                'freeDorms' => $freeDorms, 
                'commandant' => null,
                'pageTitle' => 'Добавление коменданта'
            ]);
    }

    public function commandant_update(int $id, Request $request): string
    {
        $commandant = User::where('user_id', $id)->first();

        if (!$commandant) {
            app()->route->redirect('/commandants');
        }

        $freeDorms = Dormitory::whereNull('user_id')->orWhere('user_id', $commandant->user_id)->get();

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'full_name' => ['required'],
                'login' => ['required', "unique:users,login,{$commandant->user_id},user_id"],
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if ($validator->fails()) {
                return (new View())->render('site.commandant_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'freeDorms' => $freeDorms,
                    'commandant'=> $commandant,
                    'pageTitle' => 'Редактирование коменданта'
                ]);
            }

            $data = [
                'full_name' => $request->full_name,
                'login' => $request->login,
            ];
            User::where('user_id', $id)->update($data);

            $newDormId = $request->dormitory_id;
            Dormitory::where('user_id', $id)->update(['user_id' => null]);

            if (!empty($newDormId)) {
                Dormitory::where('dormitory_id', $newDormId)->update(['user_id' => $id]);
            }
            app()->route->redirect('/commandants');
        }

        return (new View())->render('site.commandant_form', [
            'freeDorms'  => $freeDorms,
            'commandant' => $commandant,
            'pageTitle'  => 'Редактирование коменданта'
        ]);
    }

    public function commandant_delete(int $id, Request $request): string
    {
        $commandant = User::where('user_id', $id)->first();
        if (!$commandant) {
            app()->route->redirect('/commandants');
        }

        if (Dormitory::where('user_id', $id)->exists()) {
            app()->route->redirect('/commandants');
        }

        User::where('user_id', $id)->delete();
        app()->route->redirect('/commandants');
    }

    public function debtors(Request $request): string
    {
        return (new View())->render('site.debtors');
    }

    public function dormitories(Request $request): string
    {
        $dormitories = Dormitory::all();
        $commandants = User::where('role_id', 2)->get();
        $busyIds = Room::whereIn('dormitory_id', $dormitories->pluck('dormitory_id'))->pluck('dormitory_id')->unique()->toArray();

        return (new View())->render('site.dormitories', [
            'dormitories' => $dormitories,
            'commandants' => $commandants,
            'busyIds'     => $busyIds
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
                'price' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if ($validator->fails()) {
                return (new View())->render('site.dormitory_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'dormitory' => null,
                    'pageTitle' => 'Добавление общежития'
                ]);
            }

            Dormitory::create($request->all());
            app()->route->redirect('/dormitories');
        }
        return (new View())->render('site.dormitory_form', [
            'dormitory' => null,
            'pageTitle' => 'Добавление общежития'
        ]);
    }

    public function dormitory_update(int $id, Request $request): string
    {
        $dormitory = Dormitory::where('dormitory_id', $id)->first();

        if (!$dormitory) {
            app()->route->redirect('/dormitories');
        }

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'dormitory_number' => ['required', "unique:dormitories,dormitory_number,{$dormitory->dormitory_id},dormitory_id"],
                'city' => ['required'],
                'street' => ['required'],
                'building' => ['required'],
                'price' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if ($validator->fails()) {
                return (new View())->render('site.dormitory_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'dormitory' => $dormitory,
                    'pageTitle' => 'Редактирование общежития'
                ]);
            }

            $data = [
                'dormitory_number' => $request->dormitory_number,
                'city' => $request->city,
                'street' => $request->street,
                'building' => $request->building,
                'price' => $request->price
            ];

            Dormitory::where('dormitory_id', $id)->update($data);
            app()->route->redirect('/dormitories');
        }

        return (new View())->render('site.dormitory_form', [
            'dormitory' => $dormitory,
            'pageTitle' => 'Редактирование общежития'
        ]);
    }

    public function dormitory_delete(int $id, Request $request): string
    {
        $dormitory = Dormitory::where('dormitory_id', $id)->first();
        if (!$dormitory) {
            app()->route->redirect('/dormitories');
        }

        if (Room::where('dormitory_id', $id)->exists()) {
            app()->route->redirect('/dormitories');
        }

        Dormitory::where('dormitory_id', $id)->delete();
        app()->route->redirect('/dormitories');
    }

    public function residents(Request $request): string
    {
        return (new View())->render('site.residents');
    }

    public function resident_form(Request $request): string
    {
        return (new View())->render('site.resident_form');
    }

    public function rooms(Request $request): string
    {
        $rooms = Room::all();
        $types = RoomType::all();
        $dormitories = Dormitory::all();
        return (new View())->render('site.rooms', [
                'rooms' => $rooms, 
                'types' => $types,
                'dormitories' => $dormitories
            ]);
    }

    public function room_create(int $dormitory_id, Request $request): string
    {
        $types = RoomType::all();
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'room_number' => ['required'],
                'floor' => ['required'], 
                'capacity' => ['required'],
                'type_id' => ['required']
            ], ['required' => 'Поле :field пусто']);

            if ($validator->fails()) {
                return (new View())->render('site.room_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'room' => null,
                    'dormitory_id' => $dormitory_id,
                    'pageTitle' => 'Добавление комнаты',
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
            'pageTitle' => 'Добавление комнаты',
            'types' => $types
        ]);
    }

    public function room_update(int $id, Request $request): string
    {
        $room = Room::where('room_id', $id)->first();
        if (!$room) app()->route->redirect('/dormitories');

        $types = RoomType::all();
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'room_number' => ['required'],
                'floor' => ['required'],
                'capacity' => ['required'],
                'type_id' => ['required']
            ], ['required' => 'Поле :field пусто']);

            if ($validator->fails()) {
                return (new View())->render('site.room_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'room' => $room,
                    'pageTitle' => 'Редактирование комнаты',
                    'types' => $types
                ]);
            }

            $data = [
                'room_number' => $request->room_number,
                'floor' => $request->floor,
                'capacity' => $request->capacity,
                'type_id' => $request->type_id
            ];
            Room::where('room_id', $id)->update($data);
            app()->route->redirect('/rooms');
        }

        return (new View())->render('site.room_form', [
            'room' => $room,
            'pageTitle' => 'Редактирование комнаты',
            'types' => $types
        ]);
    }

    public function room_delete(int $id, Request $request): string
    {
        Room::where('room_id', $id)->delete();
        app()->route->redirect('/rooms');
    }

    // public function index(Request $request): string
    // {
    //     if ($request->id) {
    //         $posts = Post::where('id', $request->id)->get();
    //     } else {
    //         $posts = Post::all();
    //     }
    //     return (new View())->render('site.post', ['posts' => $posts]);
    // }

    // public function hello(): string
    // {
    //     return new View('site.hello', ['message' => 'hello working']);
    // }

    // public function signup(Request $request): string
    // {
    //     if ($request->method === 'POST') {

    //         $validator = new Validator($request->all(), [
    //             'name' => ['required'],
    //             'login' => ['required', 'unique:users,login'],
    //             'password' => ['required']
    //         ], [
    //             'required' => 'Поле :field пусто',
    //             'unique' => 'Поле :field должно быть уникально'
    //         ]);

    //         if($validator->fails()){
    //             return new View('site.signup',
    //                 ['message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)]);
    //         }

    //         if (User::create($request->all())) {
    //             app()->route->redirect('/login');
    //         }
    //     }
    //     return new View('site.signup');
    // }
}