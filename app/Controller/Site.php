<?php

namespace Controller;

use function Collect\collection;
use Model\Post;
use Model\User;
use Model\Dormitory;
use Model\Room;
use Model\RoomType;
use Model\Gender;
use Model\Payment;
use Model\Residence;
use Model\Resident;
use Model\ResidentStatus;
use Src\View;
use Src\Request;
use Src\Auth\Auth;
use Validator\Validator;

class Site
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
                'login' => ['required', 'unique:users,login'],
                'full_name' => ['required'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ],
            app()->settings->app['validators']);

            if ($validator->fails()) {
                return new View('site.commandant_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'free_dorms' => $free_dorms,
                    'commandant' => null,
                    'page_title' => 'Добавление коменданта'
                ]);
            }

            $data = $request->all();
            $data['role_id'] = 2;
            
            $commandant = User::create($data);
            Dormitory::assign_dormitory_to_commandant($commandant->user_id, $data['dormitory_id'] ?? null);
            
            app()->route->redirect('/commandants');
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

        return (new View())->render('site.commandant_form', [
            'free_dorms' => $free_dorms,
            'commandant' => $commandant,
            'page_title' => 'Прикрепление к общежитию'
        ]);
    }

    public function debtors(Request $request): string
    {
        return (new View())->render('site.debtors', [
            'debtors' => Resident::get_debtors(),
            'request' => $request
        ]);
    }

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

    public function residents(Request $request): string
    {
        $user_id = app()->auth->user()->getId();
        $search = $request->search ?? '';
        $sort = $request->get('residents-sort', 'alphabet_asc');
        $sort_dir = ($sort === 'alphabet_desc') ? 'desc' : 'asc';

        return (new View())->render('site.residents', [
            'residents' => Resident::get_for_dormitory_commandant($user_id, $search, $sort_dir),
            'search' => $search,
            'sort' => $sort
        ]);
    }

    public function resident_create(int $room_id, Request $request): string
    {
        $options = Resident::get_form_options();

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'last_name' => ['required'],
                'first_name' => ['required'],
                'passport' => ['required', 'passport'],
                'gender_id' => ['required'],
                'status_id' => ['required'],
                'residence_order_num' => ['required'],
                'date_of_entry' => ['required'],
                'date_of_departure' => ['required', 'date:' . $request->date_of_entry],
                'receipt_file' => ['max_file_size']
            ], [
                'required' => 'Поле :field обязательно',
                'date' => 'Поле :field не может быть <= дате заезда',
                'passport' => 'Человек с таким паспортом уже проживает в общежитии',
                'max_file_size' => 'Размер файла не должен превышать 2МБ'
            ], app()->settings->app['validators']);

            $custom_errors = [];
        
            if (!Room::is_gender_allowed($room_id, $request->gender_id)) {
                $custom_errors['gender_id'][] = 'Пол жильца не соответствует типу комнаты';
            }

            if ($validator->fails() || !empty($custom_errors)) {
                $all_errors = array_merge($validator->errors(), $custom_errors);
                
                return (new View())->render('site.resident_form', [
                    'message' => json_encode($all_errors, JSON_UNESCAPED_UNICODE),
                    'resident' => null, 'residence' => null, 'room_id' => $room_id,
                    'page_title' => 'Добавление жильца', 
                    'genders' => $options['genders'], 
                    'statuses' => $options['statuses']
                ]);
            }

            $resident = Resident::find_or_create_by_passport([
                'last_name' => $request->last_name,
                'first_name' => $request->first_name,
                'patronymic' => $request->patronymic,
                'passport' => $request->passport,
                'gender_id' => $request->gender_id,
                'status_id' => $request->status_id
            ]);

            $price = Room::get_dormitory_price($room_id);
            $receipt_path = $this->upload_receipt_file($request->files()['receipt_file'] ?? null);

            $residence = $this->create_residence(
                $resident->resident_id, $room_id,
                $request->residence_order_num, $price,
                $request->date_of_entry, $request->date_of_departure
            );

            if ($receipt_path) {
                Payment::create_or_update_for_residence($residence->residence_id, $receipt_path);
            }
            app()->route->redirect('/rooms');
        }

        return (new View())->render('site.resident_form', [
            'resident' => null, 'residence' => null, 'room_id' => $room_id,
            'page_title' => 'Добавление жильца', 'genders' => $options['genders'], 'statuses' => $options['statuses']
        ]);
    }

    public function resident_checkout(int $id, Request $request): string
    {
        $resident = Resident::find($id);
        if (!$resident) app()->route->redirect('/residents');

        $residence = $resident->get_current_residence();
        if (!$residence) app()->route->redirect('/residents');

        Residence::checkout($residence->residence_id);
        app()->route->redirect('/residents');
    }
    
    public function resident_update(int $id, Request $request): string
    {
        $resident = Resident::find($id);
        if (!$resident) app()->route->redirect('/residents');

        $residence = $resident->get_current_residence();
        $options = Resident::get_form_options(); 

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'receipt_file' => ['max_file_size']
            ], [
                'max_file_size' => 'Размер файла не должен превышать 2МБ'
            ], app()->settings->app['validators']);

            if ($validator->fails()) {
                return (new View())->render('site.resident_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'resident' => $resident, 
                    'residence' => $residence,
                    'page_title' => 'Редактирование жильца', 
                    'genders' => $options['genders'], 
                    'statuses' => $options['statuses']
                ]);
            }

            $receipt_path = $this->upload_receipt_file($request->files()['receipt_file'] ?? null);
            
            if ($receipt_path && $residence) {
                Payment::create_or_update_for_residence($residence->residence_id, $receipt_path);
            }

            app()->route->redirect('/residents');
        }

        return (new View())->render('site.resident_form', [
            'resident' => $resident, 
            'residence' => $residence,
            'page_title' => 'Редактирование жильца', 
            'genders' => $options['genders'], 
            'statuses' => $options['statuses']
        ]);
    }

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
            'rooms'       => $rooms,
            'types'       => $types,
            'dormitories' => $dormitories,
            'request'     => $request,
            'is_admin'    => $is_admin,
        ]);
    }

    public function room_create(int $dormitory_id, Request $request): string
    {
        $types = RoomType::all();
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'room_number' => ['required', 'unique_room:' . $dormitory_id],
                'floor' => ['required'], 
                'capacity' => ['required', 'is_numeric', 'positive_number'],
                'type_id' => ['required']
            ], ['required' => 'Поле :field пусто',
                'unique_room' => 'Комната с таким номером уже существует в общежитии',
                'is_numeric' => 'Поле :field должно быть числом',
                'positive_number' => 'Поле :field не должно быть <= 0'],
                app()->settings->app['validators']);

            if ($validator->fails()) {
                return (new View())->render('site.room_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'room' => null,
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

    private function upload_receipt_file(?array $file): ?string
    {
        if (empty($file['tmp_name'])) return null;

        $dir = __DIR__ . '/../../public/uploads/receipts/';

        $name = uniqid() . '_' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $config = require __DIR__ . '/../../config/path.php';
            return '/' . $config['root'] . '/uploads/receipts/' . $name;
        }
        return null;
    }

    private function create_residence(int $residentId, int $roomId, string $orderNum, float $price, string $entry_date, string $departure_date): Residence
    {
        return Residence::create([
            'resident_id' => $residentId,
            'room_id' => $roomId,
            'date_of_entry' => $entry_date,
            'date_of_departure' => $departure_date,
            'residence_order_num' => $orderNum,
            'residence_price' => $price  
        ]);
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