<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{id}', [GameController::class, 'show'])->whereNumber('id');
Route::post('/games', [GameController::class, 'store']);
Route::post('/step/{id}', [GameController::class, 'step'])->whereNumber('id');
