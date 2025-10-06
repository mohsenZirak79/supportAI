<?php

use App\Domains\Auth\Controllers\WebController;
use App\Domains\Shared\Models\User;
use App\Domains\Shared\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Domains\Role\Controllers\RoleController;
use App\Domains\Permission\Controllers\PermissionController;

use App\Domains\Auth\Controllers\AuthController;
use Illuminate\Support\Str;

Route::get('/test', function (){
    return view('user.index');
});


Route::get('/chat', function () {
    return View::make('chat.index');
})->name('chat');
Route::get('/ticket', function () {
    return View::make('tickets.index');
});


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
Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::get('/admin', [WebController::class, 'showAdmin'])->middleware('auth:sanctum', 'role:admin');
//Route::get('/chat', [WebController::class, 'showChat'])->middleware('auth:sanctum');



Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');



Route::get('/test', function () {
    return view('user.index');
});




Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::get('/admin', [WebController::class, 'showAdmin'])/*->middleware('auth:sanctum', 'role:admin')*/
;

Route::prefix('admin')->group(function () {
    Route::get('/', [WebController::class, 'showAdmin']);
    Route::get('/users', [WebController::class, 'showUsers'])->name('admin.users');
    Route::get('/roles', [WebController::class, 'showRoles'])->name('admin.roles');
    Route::get('/tickets', [WebController::class, 'showTickets'])->name('admin.tickets');
    Route::get('/chats', [WebController::class, 'showChats'])->name('admin.chats');
    Route::post('/', [WebController::class, 'showAdmin']);
    Route::put('/{id}', [WebController::class, 'showAdmin']);
    Route::delete('/{id}', [WebController::class, 'showAdmin']);
});

Route::prefix('user')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');       // لیست کاربران
    Route::get('/create', [UserController::class, 'create'])->name('create'); // فرم ایجاد
    Route::post('/', [UserController::class, 'store'])->name('store');      // ذخیره کاربر جدید
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit'); // فرم ویرایش
    Route::put('/{user}', [UserController::class, 'update'])->name('update'); // آپدیت کاربر
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy'); // حذف کاربر
});

Route::prefix('role')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');       // لیست نقش ها
    Route::get('/create', [RoleController::class, 'create'])->name('create'); // فرم ایجاد
    Route::post('/', [RoleController::class, 'store'])->name('store');      // ذخیره نقش  جدید
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit'); // فرم ویرایش
    Route::put('/{role}', [RoleController::class, 'update'])->name('update'); // آپدیت نقش
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy'); // حذف نقش
});


Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
//Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');


Route::prefix('permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::post('/', [PermissionController::class, 'store']);
    Route::put('/{id}', [PermissionController::class, 'update']);
    Route::delete('/{id}', [PermissionController::class, 'destroy']);
});

