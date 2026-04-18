<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'resident_id';

    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'passport',
        'status_id',
        'gender_id'
    ];

    public function residences()
    {
        return $this->hasMany(Residence::class, 'resident_id', 'resident_id');
    }

    public function status()
    {
        return $this->belongsTo(ResidentStatus::class, 'status_id', 'status_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'gender_id');
    }

    public function get_current_residence()
    {
        return $this->residences()->whereNull('actual_date_of_departure')->first();
    }

    public function scopeActive($query)
    {
        return $query->whereHas('residences', function ($q) {
            $q->whereNull('actual_date_of_departure');
        });
    }
}