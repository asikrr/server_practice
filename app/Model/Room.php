<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'room_id';
    
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
}