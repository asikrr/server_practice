<?php
return [
    'auth' => \Src\Auth\Auth::class,
    'identity' => \Model\User::class,
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
        'admin' => \Middlewares\AdminMiddleware::class,
        'commandant' => \Middlewares\CommandantMiddleware::class
    ],
    'validators' => [
        'required' => \Validators\RequireValidator::class,
        'unique' => \Validators\UniqueValidator::class,
        'is_numeric' => Validators\NumericValidator::class,
        'min_number'  => Validators\MinNumberValidator::class,
        'max_file_size' => Validators\MaxFileSizeValidator::class,
        'date' => Validators\DateValidator::class,
        'unique_room' => Validators\UniqueRoomValidator::class,
        'passport' => Validators\PassportValidator::class
    ],
    'routeAppMiddleware' => [
        'csrf' => \Middlewares\CSRFMiddleware::class,
        'trim' => \Middlewares\TrimMiddleware::class,
        'specialChars' => \Middlewares\SpecialCharsMiddleware::class,
    ],
];