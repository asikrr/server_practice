<?php

use Src\Route;

Route::add('GET', '/', [Controller\Site::class, 'index'])->middleware('auth');
Route::add(['GET', 'POST'], '/login', [Controller\Site::class, 'login']);
Route::add('GET', '/logout', [Controller\Site::class, 'logout']);

Route::add('GET', '/commandants', [Controller\Site::class, 'commandants'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/commandant_form', [Controller\Site::class, 'commandant_form'])->middleware('auth', 'admin');

Route::add('GET', '/debtors', [Controller\Site::class, 'debtors'])->middleware('auth', 'commandant');

Route::add('GET', '/dormitories', [Controller\Site::class, 'dormitories'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/dormitory_form', [Controller\Site::class, 'dormitory_form'])->middleware('auth', 'admin');

Route::add('GET', '/residents', [Controller\Site::class, 'residents'])->middleware('auth', 'commandant');
Route::add(['GET', 'POST'], '/resident_form', [Controller\Site::class, 'resident_form'])->middleware('auth', 'commandant');

Route::add('GET', '/rooms', [Controller\Site::class, 'rooms'])->middleware('auth');
Route::add(['GET', 'POST'], '/room_form', [Controller\Site::class, 'room_form'])->middleware('auth', 'admin');

// Route::add('GET', '/hello', [Controller\Site::class, 'hello'])
//    ->middleware('auth');
// Route::add(['GET', 'POST'], '/signup', [Controller\Site::class, 'signup']);