<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'payment_id';

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
}