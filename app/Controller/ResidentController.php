<?php

namespace Controller;

use Model\Payment;
use Model\Residence;
use Model\Resident;
use Model\Room;
use Src\Request;
use Src\View;

class ResidentController {
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
        return '';
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
}