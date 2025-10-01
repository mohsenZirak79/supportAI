<?php

use App\Domains\Shared\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use App\Domains\Auth\Controllers\AuthController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Str;

Route::get('/test', function (){
    return view('user.index');
});


Route::get('/chat', function () {
    return View::make('chat.index');
});
Route::get('/ticket', function () {
//    User::create([
//
//        'name' => 'کاربر تست',
//        'email' => 'test@example.com',
//        'password' => bcrypt('password'),
//    ]);
//    dd($user = User::first());
//    dd(auth()->user());

//    \App\Domains\Shared\Models\Ticket::create([
//        'parent_id'=>'5837ec13-b7ed-4fd6-8d84-2a752b3acc28',
//        'root_id'=>'5837ec13-b7ed-4fd6-8d84-2a752b3acc28',
//        'title'=>'پاسخ',
//        'message'=>'پاسخ',
//        'sender_type'=>'support_finance',
//        'sender_id'=>'c52c582f-e127-4776-8b06-ea6304bc930e',
//
//    ]);
    return View::make('tickets.index');
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
