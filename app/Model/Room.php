<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'room_id';
    protected $table = 'rooms';
    
    protected $fillable = [
        'dormitory_id',
        'room_number',
        'floor',
        'capacity',
        'type_id',
    ];

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class, 'dormitory_id', 'dormitory_id');
    }

    public function residences()
    {
        return $this->hasMany(Residence::class, 'room_id', 'room_id');
    }

    public function type() 
    {
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }

    public function get_current_residents_count(): int
    {
        return $this->residences()->whereNull('actual_date_of_departure')->count();
    }

    public static function get_count_by_dormitory(int $dormitory_id): int
    {
        return self::where('dormitory_id', $dormitory_id)->count();
    }

    public static function get_dormitory_price(int $room_id): float
    {
        return self::with('dormitory')->find($room_id)?->dormitory?->price ?? 0;
    }

    public static function get_filtered(int $user_id, bool $is_admin, ?string $type_filter, ?string $availability_filter)
    {
        $query = static::with([
            'dormitory',
            'type',
            'residences' => fn($q) => $q->whereNull('actual_date_of_departure')
        ]);

        if (!$is_admin) {
            $query->whereHas('dormitory', fn($q) => $q->where('user_id', $user_id));
        }

        if ($type_filter !== null && $type_filter !== '') {
            $query->where('type_id', $type_filter);
        }

        if ($availability_filter === 'available') {
            $query->whereRaw('(SELECT COUNT(*) FROM residences WHERE residences.room_id = rooms.room_id AND actual_date_of_departure IS NULL) < rooms.capacity');
        }

        return $query->get();
    }
}