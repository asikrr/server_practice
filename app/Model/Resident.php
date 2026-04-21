<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Model\Room;
use Model\Gender;
use Model\ResidentStatus;

class Resident extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'resident_id';

    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'passport',
        'status_id',
        'gender_id'
    ];

    public function residences()
    {
        return $this->hasMany(Residence::class, 'resident_id', 'resident_id');
    }

    public function status()
    {
        return $this->belongsTo(ResidentStatus::class, 'status_id', 'status_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'gender_id');
    }

    public function get_current_residence()
    {
        return $this->residences()->whereNull('actual_date_of_departure')->first();
    }

    public function scopeActive($query)
    {
        return $query->whereHas('residences', function ($q) {
            $q->whereNull('actual_date_of_departure');
        });
    }

    public static function find_or_create_by_passport(array $data): self
    {
        $existing = self::where('passport', $data['passport'])->first();
        return $existing ?? self::create($data);
    }

    public static function update_resident_data(int $id, array $data): void
    {
        self::where('resident_id', $id)->update($data);
    }

    public static function get_form_options(): array
    {
        return [
            'genders' => Gender::all(),
            'statuses' => ResidentStatus::all()
        ];
    }

    public static function get_debtors(int $commandant_id): array
    {
        $room_ids_query = Room::whereHas('dormitory', fn($q) => $q->where('user_id', $commandant_id))
                              ->select('room_id');

        return self::with(['residences' => fn($q) => $q->whereNull('actual_date_of_departure')->with(['room', 'payment'])])
            ->whereHas('residences', fn($q) => $q->whereNull('actual_date_of_departure')
                                                 ->whereIn('room_id', $room_ids_query)) 
            ->get()
            ->filter(fn($r) => $r->residences->first() && !$r->residences->first()->payment)
            ->map(fn($r) => [
                'resident' => $r,
                'room_number' => $r->residences->first()->room->room_number,
                'debt' => $r->residences->first()->residence_price
            ])
            ->values() 
            ->toArray();
    }

    public function scopeSearch($query, string $search)
    {
        if ($search === '') return $query;
        return $query->where(function($q) use ($search) {
            $q->where('last_name', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('patronymic', 'like', "%{$search}%");
        });
    }

    public static function get_for_dormitory_commandant(int $user_id, string $search = '', string $sort_dir = 'asc')
    {
        $room_ids_query = Room::whereHas('dormitory', fn($q) => $q->where('user_id', $user_id))->select('room_id');

        return self::active()
            ->whereHas('residences', fn($q) => $q->whereIn('room_id', $room_ids_query))
            ->with([
                'gender', 
                'status', 
                'residences' => fn($q) => $q->whereNull('actual_date_of_departure')->with(['room', 'payment'])
            ])
            ->search($search)
            ->orderBy('last_name', $sort_dir)
            ->orderBy('first_name', $sort_dir)
            ->get();
    }
}