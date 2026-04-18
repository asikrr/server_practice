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

    public function get_current_residents_count(): int
    {
        return $this->residences()->whereNull('actual_date_of_departure')->count();
    }
}