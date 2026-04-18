<?php
namespace Model;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'gender_id';
}