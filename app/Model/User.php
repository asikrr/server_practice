<?php

namespace Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Auth\IdentityInterface;

class User extends Model implements IdentityInterface
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $timestamps = false;
    protected $fillable = [
        'full_name',
        'login',
        'password',
        'role_id'
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            $user->password = md5($user->password);
            $user->save();
        });
    }

    public function findIdentity(int $id)
    {
        return self::where('user_id', $id)->first();
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public function attemptIdentity(array $credentials)
    {
        return self::where(['login' => $credentials['login'],
            'password' => md5($credentials['password'])])->first();
    }

    public static function find_by_id(int $id): ?self
    {
        return self::where('user_id', $id)->first();
    }

    public static function get_commandants()
    {
        return self::where('role_id', 2)->get();
    }

    public static function get_commandants_count(): int
    {
        return self::where('role_id', 2)->count();
    }
}