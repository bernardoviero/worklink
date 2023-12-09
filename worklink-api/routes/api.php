<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/api/usuarios', [UserController::class, 'getAllUsers']);
Route::get('/api/trabalhos', [WorkController::class, 'getAllWorks']);
Route::get('/api/trabalhos/{type}', [WorkController::class, 'getWorksByType']);
Route::post('/api/trabalho', [WorkController::class, 'postNewWork']);
