<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class Dormitory extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'dormitory_id';
    protected $table = 'dormitories';
    
    protected $fillable = [
        'dormitory_number',
        'city',
        'street',
        'building',
        'price',
        'user_id'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'dormitory_id', 'dormitory_id');
    }

    public function commandant()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function get_rooms_count(): int
    {
        return $this->rooms()->count();
    }

    public static function get_available_for_commandant(int $commandant_id)
    {
        return self::whereNull('user_id')
                ->orWhere('user_id', $commandant_id)
                ->get();
    }

    public static function get_free_dormitories()
    {
        return self::whereNull('user_id')->get();
    }

    public static function assign_dormitory_to_commandant(int $commandant_id, ?int $dormitory_id): void
    {
        if ($commandant_id > 0) {
            self::where('user_id', $commandant_id)->update(['user_id' => null]);
        }

        if (!empty($dormitory_id)) {
            self::where('dormitory_id', $dormitory_id)->update(['user_id' => $commandant_id]);
        }
    }

    public static function get_commandant_dormitories()
    {
        return self::whereIn('user_id', User::where('role_id', 2)->select('user_id'))->get();
    }

    public static function get_all_with_commandants()
    {
        return self::with('commandant')->get();
    }

    public static function find_by_commandant(int $user_id): ?self
    {
        return self::where('user_id', $user_id)->first();
    }
}