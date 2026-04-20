<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'payment_id';
    protected $table = 'payments';

    protected $fillable = [
        'date',
        'amount',
        'residence_id',
        'receipt_file'
    ];

    public function residence()
    {
        return $this->belongsTo(Residence::class, 'residence_id', 'residence_id');
    }

    public static function create_or_update_for_residence(int $residence_id, string $receipt_path): void
    {
        $residence = Residence::with('room.dormitory')->find($residence_id);
        if (!$residence) return;

        self::updateOrCreate(
            ['residence_id' => $residence_id],
            [
                'date' => date('Y-m-d'),
                'amount' => $residence->room->dormitory->price,
                'receipt_file' => $receipt_path
            ]
        );
    }
}