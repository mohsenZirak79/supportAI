<?php

use App\Domains\Auth\Controllers\WebController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use App\Domains\Auth\Controllers\AuthController;
use App\Domain\Role\Controllers\RoleController;
use App\Domain\Permission\Controllers\PermissionController;

Route::get('/test', function (){
    return view('user.index');
});


Route::get('/chat', function () {
    return View::make('chat.index');
});


Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::get('/admin', [WebController::class, 'showAdmin'])/*->middleware('auth:sanctum', 'role:admin')*/;
//Route::get('/chat', [WebController::class, 'showChat'])->middleware('auth:sanctum');

Route::prefix('admin')->group(function () {
    Route::get('/', [WebController::class, 'showAdmin']);
    Route::get('/users', [WebController::class, 'showUsers'])->name('admin.users');
    Route::get('/roles', [WebController::class, 'showRoles'])->name('admin.roles');
    Route::get('/tickets', [WebController::class, 'showTickets'])->name('admin.tickets');
    Route::post('/', [WebController::class, 'showAdmin']);
    Route::put('/{id}', [WebController::class, 'showAdmin']);
    Route::delete('/{id}', [WebController::class, 'showAdmin']);
});






Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
//Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');


Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::put('/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
});

Route::prefix('permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::post('/', [PermissionController::class, 'store']);
    Route::put('/{id}', [PermissionController::class, 'update']);
    Route::delete('/{id}', [PermissionController::class, 'destroy']);
});
