<?php

namespace Src\Auth;

use Src\Session;

class Auth
{
    private static ?IdentityInterface $resolver = null;
    private static ?IdentityInterface $authenticatedUser = null;

    public static function init(IdentityInterface $user): void
    {
        self::$resolver = $user;
    }

    public static function login(IdentityInterface $user): void
    {
        self::$authenticatedUser = $user;
        Session::set('id', $user->getId());
        Session::set('role_id', $user->role_id);
    }

    public static function attempt(array $credentials): bool
    {
        if (self::$resolver && $user = self::$resolver->attemptIdentity($credentials)) {
            self::login($user);
            return true;
        }
        return false;
    }

    public static function user(): ?IdentityInterface
    {
        if (self::$authenticatedUser !== null) {
            return self::$authenticatedUser;
        }

        $id = Session::get('id') ?? 0;
        if ($id > 0 && self::$resolver) {
            $found = self::$resolver->findIdentity($id);
            if ($found) {
                self::$authenticatedUser = $found;
                return $found;
            }
        }

        return null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function generateCSRF(): string
    {
        $token = md5(time());
        Session::set('csrf_token', $token);
        return $token;
    }

    public static function logout(): bool
    {
        Session::clear('id');
        Session::clear('role_id');
        self::$authenticatedUser = null;
        return true;
    }
}