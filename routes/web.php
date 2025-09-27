<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use App\Domains\Auth\Controllers\AuthController;
use App\Http\Controllers\WebController;
Route::get('/test', function (){
    return view('user.index');
});


Route::get('/chat', function () {
    return View::make('chat.index');
});


Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::get('/admin', [WebController::class, 'showAdmin'])->middleware('auth:sanctum', 'role:admin');
//Route::get('/chat', [WebController::class, 'showChat'])->middleware('auth:sanctum');



Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');
