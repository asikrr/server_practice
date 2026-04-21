<?php

use Src\Route;

Route::add('GET', '/', [Controller\IndexController::class, 'index'])->middleware('auth');

Route::add(['GET', 'POST'], '/login', [Controller\AuthController::class, 'login']);
Route::add('GET', '/logout', [Controller\AuthController::class, 'logout']);

Route::add('GET', '/commandants', [Controller\CommandantController::class, 'commandants'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/commandant_create', [Controller\CommandantController::class, 'commandant_create'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/commandant_update/{id}', [Controller\CommandantController::class, 'commandant_update'])->middleware('auth', 'admin');

Route::add('GET', '/debtors', [Controller\DebtorsController::class, 'debtors'])->middleware('auth', 'commandant');

Route::add('GET', '/dormitories', [Controller\DormitoryController::class, 'dormitories'])->middleware('auth', 'admin');
Route::add(['GET', 'POST'], '/dormitory_create', [Controller\DormitoryController::class, 'dormitory_create'])->middleware('auth', 'admin');

Route::add('GET', '/residents', [Controller\ResidentController::class, 'residents'])->middleware('auth', 'commandant');
Route::add(['GET', 'POST'], '/resident_create/{room_id}', [Controller\ResidentController::class, 'resident_create'])->middleware('auth', 'commandant');
Route::add(['GET', 'POST'], '/resident_update/{id}', [Controller\ResidentController::class, 'resident_update'])->middleware('auth', 'commandant');
Route::add('POST', '/resident_checkout/{id}', [Controller\ResidentController::class, 'resident_checkout'])->middleware('auth', 'commandant');

Route::add('GET', '/rooms', [Controller\RoomController::class, 'rooms'])->middleware('auth');
Route::add(['GET', 'POST'], '/room_create/{dormitory_id}', [Controller\RoomController::class, 'room_create'])->middleware('auth', 'admin');

// Route::add('GET', '/hello', [Controller\Site::class, 'hello'])
//    ->middleware('auth');
// Route::add(['GET', 'POST'], '/signup', [Controller\Site::class, 'signup']);