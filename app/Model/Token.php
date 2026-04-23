<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    public $timestamps = false;
    protected $table = 'api_tokens';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'token'
    ];
}