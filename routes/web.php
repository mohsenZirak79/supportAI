<?php

use App\Domains\AdminPanel\Controllers\AdminChatController;
use App\Domains\AdminPanel\Controllers\AdminTicketController;
use App\Domains\Auth\Controllers\WebController;
use App\Domains\Shared\Models\User;
use App\Domains\Shared\Controllers\UserController;
use App\Domains\AdminPanel\Controllers\ProfileController;
use App\Http\Middleware\CheckPermissionForRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Domains\Role\Controllers\RoleController;
use App\Domains\Permission\Controllers\PermissionController;

use App\Domains\Auth\Controllers\AuthController;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Landing page - redirects authenticated users to dashboard
Route::get('/', [\App\Domains\Auth\Controllers\LandingController::class, 'index'])->name('landing');

// Landing page auth routes (email/password with remember me)
Route::post('/landing/login', [\App\Domains\Auth\Controllers\LandingController::class, 'login'])->name('landing.login');
Route::post('/landing/register', [\App\Domains\Auth\Controllers\LandingController::class, 'register'])->name('landing.register');

Route::get('/chat', fn() => View::make('chat.index'))->name('chat')->middleware('ensure.jwt.cookie');
Route::get('/ticket', fn() => View::make('tickets.index'))->middleware('ensure.jwt.cookie');

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

//Route::get('/chat', [WebController::class, 'showChat'])->middleware('auth:sanctum');


Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
//Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');


Route::get('/test', function () {
    return view('user.index');
});


Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');;

Route::middleware(['web', 'auth:web', \App\Http\Middleware\CheckPermissionForRoute::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/users', [WebController::class, 'showUsers'])->name('admin.users');
        Route::get('/roles', [WebController::class, 'showRoles'])->name('admin.roles');
        Route::get('/tickets', [\App\Domains\AdminPanel\Controllers\AdminTicketController::class, 'index'])->name('admin.tickets');
        Route::get('/tickets/{id}', [\App\Domains\AdminPanel\Controllers\AdminTicketController::class, 'show'])->name('admin.tickets.show');
        Route::post('/tickets/{id}/messages', [\App\Domains\AdminPanel\Controllers\AdminTicketController::class, 'reply'])->name('admin.tickets.reply');
        Route::get('/chats', [AdminChatController::class, 'index'])->name('admin.chats');
        Route::get('dashboard', function (){
            return \view('admin.dashboard');
        })->name('admin.dashboard');
    });

Route::prefix('admin')->group(function () {
    Route::get('/chats/{conversation}/detail', [AdminChatController::class, 'detail'])->name('admin.chats.detail');
    Route::post('/referrals/{referral}/respond', [AdminChatController::class, 'respond'])->name('admin.referrals.respond');
    Route::post('/referrals/{referral}/assign-me', [AdminChatController::class, 'assignMe'])->name('admin.referrals.assign_me');
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


//Route::view('/register', 'auth.register')->name('register');
Route::post('/login', [AuthController::class, 'verifyLoginOtp'])->name('login.verify');
Route::post('/register', [AuthController::class, 'register'])->name('register');
//Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
