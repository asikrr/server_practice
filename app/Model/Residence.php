<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'residence_id';
    protected $table = 'residences';

    protected $fillable = [
        'resident_id',
        'room_id',
        'date_of_entry',
        'date_of_departure',
        'actual_date_of_departure',
        'residence_price',
        'residence_order_num'
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'residence_id', 'residence_id');
    }

    public static function checkout(int $residence_id): void
    {
        self::where('residence_id', $residence_id)
            ->update(['actual_date_of_departure' => date('Y-m-d')]);
    }
}