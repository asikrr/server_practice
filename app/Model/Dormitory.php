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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function get_rooms_count(): int
    {
        return $this->rooms()->count();
    }
}