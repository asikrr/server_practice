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
}