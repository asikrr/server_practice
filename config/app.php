<?php
return [
    'auth' => \Src\Auth\Auth::class,
    'identity' => \Model\User::class,
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
        'admin' => \Middlewares\AdminMiddleware::class,
        'commandant' => \Middlewares\CommandantMiddleware::class,
        'token' => \Middlewares\TokenMiddleware::class,
    ],
    'validators' => [
        'required' => \Validator\Validators\RequireValidator::class,
        'is_numeric' => \Validator\Validators\IsNumericValidator::class,
        'positive_number' => \Validator\Validators\PositiveNumberValidator::class,
        'unique' => \Validators\UniqueValidator::class,
        'max_file_size' => Validators\MaxFileSizeValidator::class,
        'date' => Validators\DateValidator::class,
        'unique_room' => Validators\UniqueRoomValidator::class,
        'passport' => Validators\PassportValidator::class,
        'max_length' => Validators\MaxLengthValidator::class
    ],
    'routeAppMiddleware' => [
        'csrf' => \Middlewares\CSRFMiddleware::class,
        'specialChars' => \Middlewares\SpecialCharsMiddleware::class,
        'trim' => \Middlewares\TrimMiddleware::class,
        'json' => \Middlewares\JSONMiddleware::class,
    ],
    'providers' => [
        'kernel' => \Providers\KernelProvider::class,
        'route' => \Providers\RouteProvider::class,
        'db' => \Providers\DBProvider::class,
        'auth' => \Providers\AuthProvider::class,
    ],
];