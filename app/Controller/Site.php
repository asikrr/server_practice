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
use Src\Validator\Validator;

class Site
{
    public function index(Request $request): string
    {
        $user = app()->auth->user();
        $role_id = $user->role_id; 
        
        $data = [
            'user_name' => $user->full_name,
            'role_id'   => $role_id
        ];

        if ($role_id == 1) {
            $data['dormitories_count'] = Dormitory::count();
            $data['commandants_count'] = User::where('role_id', 2)->count();
        } 

        elseif ($role_id == 2) {
            $dormitory = Dormitory::where('user_id', $user->user_id)->first();
            $data['dormitory'] = $dormitory;
            $data['rooms_count'] = $dormitory ? Room::where('dormitory_id', $dormitory->dormitory_id)->count() : 0;
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
        $commandants = User::where('role_id', 2)->get();
        $dormitories = Dormitory::whereIn('user_id', $commandants->pluck('user_id'))->get();
        $busy_ids = $dormitories->pluck('user_id')->unique()->toArray();

        return (new View())->render('site.commandants', [
            'commandants' => $commandants,
            'dormitories' => $dormitories,
            'busy_ids' => $busy_ids 
        ]);
    }

    public function commandant_create(Request $request): string
    {
        $free_dorms = Dormitory::whereNull('user_id')->get();

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
                    'free_dorms' => $free_dorms,
                    'commandant' => null,
                    'page_title' => 'Добавление коменданта'
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
                'free_dorms' => $free_dorms, 
                'commandant' => null,
                'page_title' => 'Добавление коменданта'
            ]);
    }

    public function commandant_update(int $id, Request $request): string
    {
        $commandant = User::where('user_id', $id)->first();

        if (!$commandant) {
            app()->route->redirect('/commandants');
        }

        $free_dorms = Dormitory::whereNull('user_id')->orWhere('user_id', $commandant->user_id)->get();

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
                    'free_dorms' => $free_dorms,
                    'commandant'=> $commandant,
                    'page_title' => 'Редактирование коменданта'
                ]);
            }

            $data = [
                'full_name' => $request->full_name,
                'login' => $request->login,
            ];
            User::where('user_id', $id)->update($data);

            $new_dorm_id = $request->dormitory_id;
            Dormitory::where('user_id', $id)->update(['user_id' => null]);

            if (!empty($new_dorm_id)) {
                Dormitory::where('dormitory_id', $new_dorm_id)->update(['user_id' => $id]);
            }
            app()->route->redirect('/commandants');
        }

        return (new View())->render('site.commandant_form', [
            'free_dorms'  => $free_dorms,
            'commandant' => $commandant,
            'page_title'  => 'Редактирование коменданта'
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
        $activeResidents = Resident::active()
            ->with(['residences' => fn($q) => $q->whereNull('actual_date_of_departure')->with(['room', 'payment'])])
            ->get();

        $debtors = collection($activeResidents->all())
            ->filter(fn($r) => $r->residences->first() && !$r->residences->first()->payment)
            ->map(fn($r) => [
                'resident' => $r,
                'room_number' => $r->residences->first()->room->room_number,
                'debt' => $r->residences->first()->residence_price
            ])
            ->toArray(); 

        return (new View())->render('site.debtors', [
            'debtors' => $debtors,
            'request' => $request
        ]);
    }

    public function dormitories(Request $request): string
    {
        $dormitories = Dormitory::with('commandant')->get();
        $commandants = User::where('role_id', 2)->get();
        $busy_ids = Room::whereIn('dormitory_id', $dormitories->pluck('dormitory_id'))->pluck('dormitory_id')->unique()->toArray();

        return (new View())->render('site.dormitories', [
            'dormitories' => $dormitories,
            'commandants' => $commandants,
            'busy_ids' => $busy_ids
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
                'price' => ['required', 'is_numeric', 'min_number']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально',
                'is_numeric' => 'Поле :field должно быть числом',
                'min_number' => 'Поле :field не должно быть <= 0'
            ]);

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
                'price' => ['required', 'is_numeric', 'min_number']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально',
                'is_numeric' => 'Поле :field должно быть числом',
                'min_number' => 'Поле :field не должно быть <= 0'
            ]);

            if ($validator->fails()) {
                return (new View())->render('site.dormitory_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'dormitory' => $dormitory,
                    'page_title' => 'Редактирование общежития'
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
            'page_title' => 'Редактирование общежития'
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
        $user_id = app()->auth->user()->getId();
        $search = $request->search ?? '';
        $sort = $request->get('residents-sort', 'alphabet_asc');
        $sort_dir = ($sort === 'alphabet_desc') ? 'desc' : 'asc';
        $room_ids = Room::whereHas('dormitory', fn($q) => $q->where('user_id', $user_id))->pluck('room_id');

        $query = Resident::active()
            ->whereHas('residences', fn($q) => $q->whereIn('room_id', $room_ids))
            ->with([
                'gender', 
                'status', 
                'residences' => fn($q) => $q->whereNull('actual_date_of_departure')->with(['room', 'payment'])]);

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('patronymic', 'like', "%{$search}%");
            });
        }

        $query->orderBy('last_name', $sort_dir)->orderBy('first_name', $sort_dir);

        $residents = $query->get();

        return (new View())->render('site.residents', [
            'residents' => $residents,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    public function resident_create(int $room_id, Request $request): string
    {
        $genders = Gender::all();
        $statuses = ResidentStatus::all();

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'last_name' => ['required'],
                'first_name' => ['required'],
                'patronymic' => ['required'],
                'passport' => ['required', 'passport'],
                'gender_id' => ['required'],
                'status_id' => ['required'],
                'residence_order_num' => ['required'],
                'date_of_entry' => ['required'],
                'date_of_departure' => ['required', 'date:' . $request->date_of_entry],
                'receipt_file' => ['max_file_size']
            ], ['required' => 'Поле :field обязательно',
                'date' => 'Поле :field не может быть <= дате заезда',
                'passport' => 'Человек с таким паспортом уже проживает в общежитии',
                'max_file_size' => 'Размер файла не должен превышать 2МБ']);

            if ($validator->fails()) {
                return (new View())->render('site.resident_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'resident' => null, 'residence' => null, 'room_id' => $room_id,
                    'page_title' => 'Добавление жильца', 'genders' => $genders, 'statuses' => $statuses
                ]);
            }

            $existing = Resident::where('passport', $request->passport)->first();
            $resident = $existing ?? Resident::create([
                'last_name' => $request->last_name,
                'first_name' => $request->first_name,
                'patronymic' => $request->patronymic,
                'passport' => $request->passport,
                'gender_id' => $request->gender_id,
                'status_id' => $request->status_id
            ]);

            $room = Room::with('dormitory')->find($room_id);
            $price = $room->dormitory->price;

            $entry_date = $request->date_of_entry;
            $departure_date = $request->date_of_departure;

            $receipt_path = $this->upload_receipt_file($request->files()['receipt_file'] ?? null);
            $residence = $this->create_residence(
                $resident->resident_id,
                $room_id,
                $request->residence_order_num,
                $price,
                $entry_date,
                $departure_date
            );

            $this->create_payment($residence->residence_id, $receipt_path);

            app()->route->redirect('/rooms');
        }

        return (new View())->render('site.resident_form', [
            'resident' => null, 'residence' => null, 'room_id' => $room_id,
            'page_title' => 'Добавление жильца', 'genders' => $genders, 'statuses' => $statuses
        ]);
    }

    public function resident_checkout(int $id, Request $request): string
    {
        $resident = Resident::find($id);
        if (!$resident) {
            app()->route->redirect('/residents');
        }

        $residence = $resident->get_current_residence();
        if (!$residence) {
            app()->route->redirect('/residents');
        }

        $residence->update([
            'actual_date_of_departure' => date('Y-m-d')
        ]);

        app()->route->redirect('/residents');
    }

    public function resident_update(int $id, Request $request): string
    {
        $resident = Resident::where('resident_id', $id)->first();
        if (!$resident) app()->route->redirect('/residents');

        $residence = $resident->get_current_residence();
        $genders = Gender::all();
        $statuses = ResidentStatus::all();

        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'last_name' => ['required'],
                'first_name' => ['required'],
                'patronymic' => ['required'],
                'passport' => ['required', 'unique:residents,passport'],
                'status_id' => ['required'],
                'residence_order_num' => ['required'],
                'date_of_departure' => ['required', 'date:' . $request->date_of_entry],
                'receipt_file' => ['max_file_size']
            ], ['required' => 'Поле :field обязательно',
                'unique' => 'Поле :field должно быть уникально',
                'passport' => 'Этот паспорт уже существует в БД',
                'date' => 'Поле :field не может быть <= дате заезда',
                'max_file_size' => 'Размер файла не должен превышать 2МБ']);

            if ($validator->fails()) {
                return (new View())->render('site.resident_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'resident' => $resident, 'residence' => $residence,
                    'page_title' => 'Редактирование жильца', 'genders' => $genders, 'statuses' => $statuses
                ]);
            }

            Resident::where('resident_id', $id)->update([
                'last_name' => $request->last_name,
                'first_name' => $request->first_name,
                'patronymic' => $request->patronymic,
                'passport' => $request->passport,
                'status_id' => $request->status_id
            ]);

            if ($residence) {
                Residence::where('residence_id', $residence->residence_id)->update([
                    'residence_order_num' => $request->residence_order_num
                ]);

                $receipt_path = $this->upload_receipt_file($request->files()['receipt_file'] ?? null);
                if ($receipt_path) {
                    $this->create_payment($residence->residence_id, $receipt_path);
                }
            }

            app()->route->redirect('/residents');
        }

        return (new View())->render('site.resident_form', [
            'resident' => $resident, 'residence' => $residence,
            'page_title' => 'Редактирование жильца', 'genders' => $genders, 'statuses' => $statuses
        ]);
    }

    public function rooms(Request $request): string
    {
        $user = app()->auth->user(); 
        $is_admin = $user->role_id == 1; 

        $query = Room::with([
            'dormitory', 
            'type', 
            'residences' => fn($q) => $q->whereNull('actual_date_of_departure')
        ]);

        if (!$is_admin) {
            $query->whereHas('dormitory', fn($q) => $q->where('user_id', $user->getId()));
        }

        if ($request->type_filter !== null && $request->type_filter !== '') {
            $query->where('type_id', $request->type_filter);
        }

        if ($request->availability_filter === 'available') {
            $query->whereRaw('(SELECT COUNT(*) FROM residences WHERE residences.room_id = rooms.room_id AND actual_date_of_departure IS NULL) < rooms.capacity');
        }

        $rooms = $query->with(['dormitory', 'type'])->get(); 
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
                'room_number' => ['required', 'unique_room:' . $dormitory_id],
                'floor' => ['required'], 
                'capacity' => ['required', 'is_numeric', 'min_number'],
                'type_id' => ['required']
            ], ['required' => 'Поле :field пусто',
                'unique_room' => 'Комната с таким номером уже существует в общежитии',
                'is_numeric' => 'Поле :field должно быть числом',
                'min_number' => 'Поле :field не должно быть <= 0']);

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

    public function room_update(int $id, Request $request): string
    {
        $room = Room::find($id);
        if (!$room) app()->route->redirect('/rooms');

        if ($room->get_current_residents_count() > 0) {
            app()->route->redirect('/rooms');
        }

        $types = RoomType::all();
        if ($request->method === 'POST') {
            $validator = new Validator($request->all(), [
                'room_number' => ['required', 'unique_room:' . $dormitory_id . ',' . $room->room_id],
                'floor' => ['required'],
                'capacity' => ['required', 'is_numeric', 'min_number'],
                'type_id' => ['required']
            ], ['required' => 'Поле :field пусто',
                'unique_room' => 'Комната с таким номером уже существует в общежитии',
                'is_numeric' => 'Поле :field должно быть числом',
                'min_number' => 'Поле :field не должно быть <= 0']);

            if ($validator->fails()) {
                return (new View())->render('site.room_form', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE),
                    'room' => $room,
                    'page_title' => 'Редактирование комнаты',
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
            'page_title' => 'Редактирование комнаты',
            'types' => $types
        ]);
    }

    public function room_delete(int $id, Request $request): string
    {
        $room = Room::find($id);
        if (!$room) app()->route->redirect('/rooms');

        if ($room->get_current_residents_count() > 0) {
            app()->route->redirect('/rooms');
        }
        
        Room::where('room_id', $id)->delete();
        app()->route->redirect('/rooms');
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

    private function create_residence(int $residentId, int $roomId, string $orderNum, float $price, ?string $entry_date, ?string $departure_date): Residence
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

    private function create_payment(int $residence_id, ?string $receipt_path): void
    {
        if (!$receipt_path) return;

        $residence = Residence::with('room.dormitory')->find($residence_id);
        $amount = $residence->room->dormitory->price;

        Payment::updateOrCreate(
            ['residence_id' => $residence_id],
            [                                 
                'date' => date('Y-m-d'),
                'amount' => $amount,
                'receipt_file' => $receipt_path
            ]
        );
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