<?php
use PHPUnit\Framework\TestCase;
use Model\Room;
use Model\Dormitory;
use Model\User;

class RoomTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs/practice_without_packages';
        $config = include $_SERVER['DOCUMENT_ROOT'] . '/config/app.php';
        $GLOBALS['app'] = new \Src\Application($config);

        if (!function_exists('app')) {
            function app() {
                return $GLOBALS['app'];
            }
        }
    }

    public function testGetFilteredByTypeAndAvailability(): void
    {
        $user = User::create([
            'login' => 'test_room_user_' . time(),
            'password' => '123',
            'full_name' => 'Test User',
            'role_id' => 1
        ]);

        $dorm = Dormitory::create([
            'dormitory_number' => '12345',
            'city' => 'Test City',
            'street' => 'Test Street',
            'building' => '1',
            'price' => 0,
            'user_id' => $user->user_id
        ]);

        $room_male_empty = Room::create([
            'dormitory_id' => $dorm->dormitory_id,
            'room_number' => '101',
            'floor' => 1,
            'capacity' => 2,
            'type_id' => 1
        ]);

        $room_female_empty = Room::create([
            'dormitory_id' => $dorm->dormitory_id,
            'room_number' => '102',
            'floor' => 1,
            'capacity' => 1,
            'type_id' => 2
        ]);

        $room_male_full = Room::create([
            'dormitory_id' => $dorm->dormitory_id,
            'room_number' => '103',
            'floor' => 1,
            'capacity' => 0,
            'type_id' => 1
        ]);

        try {
            $female_rooms = Room::get_filtered($user->user_id, false, '2', null);
            $this->assertCount(1, $female_rooms);
            $this->assertEquals($room_female_empty->room_id, $female_rooms->first()->room_id);

            $available_rooms = Room::get_filtered($user->user_id, false, null, 'available');
            $this->assertCount(2, $available_rooms);

            $male_available = Room::get_filtered($user->user_id, false, '1', 'available');
            $this->assertCount(1, $male_available);
            $this->assertEquals($room_male_empty->room_id, $male_available->first()->room_id);
        } finally {
            Room::where('dormitory_id', $dorm->dormitory_id)->delete();
            Dormitory::where('dormitory_id', $dorm->dormitory_id)->delete();
            User::where('user_id', $user->user_id)->delete();
        }
    }
}