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

    public static function create_once(int $residence_id, array $data): ?self
    {
        if (self::where('residence_id', $residence_id)->exists()) {
            return null;
        }
        
        return self::create([
            'residence_id' => $residence_id,
            'date' => $data['date'] ?? date('Y-m-d'),
            'amount' => $data['amount'] ?? 0,
            'receipt_file' => $data['receipt_file'] ?? null
        ]);
    }
}