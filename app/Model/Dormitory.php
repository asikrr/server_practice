<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class Dormitory extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'dormitory_id';
    
    protected $fillable = [
        'dormitory_number',
        'city',
        'street',
        'building',
        'price',
        'user_id'
    ];
}