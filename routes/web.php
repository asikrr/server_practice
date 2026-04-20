<?php

use Src\Route;

Route::add('GET', '/', [Controller\Site::class, 'index'])->middleware('auth');
Route::add(['GET', 'POST'], '/login', [Controller\Site::class, 'login']);
Route::add('GET', '/logout', [Controller\Site::class, 'logout']);

Route::add('GET', '/commandants', [Controller\Site::class, 'commandants'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/commandant_create', [Controller\Site::class, 'commandant_create'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/commandant_update/{id}', [Controller\Site::class, 'commandant_update'])->middleware('auth', 'admin');

Route::add('GET', '/debtors', [Controller\Site::class, 'debtors'])->middleware('auth', 'commandant');

Route::add('GET', '/dormitories', [Controller\Site::class, 'dormitories'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/dormitory_create', [Controller\Site::class, 'dormitory_create'])->middleware('auth', 'admin');

Route::add('GET', '/residents', [Controller\Site::class, 'residents'])->middleware('auth', 'commandant');
Route::add(['GET', 'POST'], '/resident_create/{room_id}', [Controller\Site::class, 'resident_create'])->middleware('auth', 'commandant');
Route::add(['GET', 'POST'], '/resident_update/{id}', [Controller\Site::class, 'resident_update'])->middleware('auth', 'commandant');
Route::add('POST', '/resident_checkout/{id}', [Controller\Site::class, 'resident_checkout'])->middleware('auth', 'commandant');

Route::add('GET', '/rooms', [Controller\Site::class, 'rooms'])->middleware('auth');
Route::add(['GET', 'POST'], '/room_create/{dormitory_id}', [Controller\Site::class, 'room_create'])->middleware('auth', 'admin');

// Route::add('GET', '/hello', [Controller\Site::class, 'hello'])
//    ->middleware('auth');
// Route::add(['GET', 'POST'], '/signup', [Controller\Site::class, 'signup']);