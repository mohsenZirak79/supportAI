<?php

use App\Domains\AdminPanel\Controllers\AdminChatController;
use App\Domains\Auth\Controllers\WebController;
use App\Domains\Shared\Models\User;
use App\Domains\Shared\Controllers\UserController;
use App\Domains\AdminPanel\Controllers\ProfileController;
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

Route::get('/', function () {
//    User::create([
//      'phone'=>'09129876543',
//      'name'=>'محسن',
//      'family'=>'زیرک',
//      'email'=>'mohsen@gmail.com'
//    ]);


//    $roles = ['برنامه نویس', 'ادمین' , 'کاربر عادی'];
//    foreach ($roles as $role) {
//        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
//    }
//
//    // 2. پرمیشن‌ها
//    $permissions = [
//        // CRUD یوزر
//        'create-user', 'read-user', 'update-user', 'delete-user',
//        // CRUD تیکت
//        'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
//        // چت
//        'create-chat', 'read-chat', 'update-chat', 'delete-chat',
//        // Role
//        'create-role', 'read-role', 'update-role', 'delete-role',
//    ];
//
//    foreach ($permissions as $permission) {
//        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
//    }
//
//    // 3. تخصیص پرمیشن‌ها به رول‌ها
//    $programmer = Role::where('name', 'برنامه نویس')->first();
//    $programmer->syncPermissions(Permission::all());
//
//    $admin = Role::where('name', 'ادمین')->first();
//    $admin->syncPermissions(Permission::all());
//
//    /*$support = Role::where('name', 'پشتیبان')->first();
//    $support->syncPermissions([
//        'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
//        'create-chat', 'read-chat', 'update-chat', 'delete-chat',
//    ]);*/
//
//    // 4. یوزرهای تستی + assign role
//    $users = [
//        ['name' => 'admin', 'password' => '123456789' , 'role' => 'ادمین'],
//        ['name' => 'programmer','password' => '123456789' , 'role' => 'برنامه نویس']
//    ];
//
//    foreach ($users as $u) {
//        $user = User::updateOrCreate(
//            [
//                'name' => $u['name'],
//                'password' => Hash::make($u['password'])
//            ]
//        );
//
//        // اتصال یوزر به نقش
//        $user->assignRole($u['role']);
//        }
    return redirect()->route('login');
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

    Route::get('/chats', [AdminChatController::class, 'index'])->name('admin.chats');
    Route::get('/chats/{conversation}/detail', [AdminChatController::class, 'detail'])->name('admin.chats.detail');
    Route::post('/referrals/{referral}/respond', [AdminChatController::class, 'respond'])->name('admin.referrals.respond');
    Route::post('/referrals/{referral}/assign-me', [AdminChatController::class, 'assignMe'])->name('admin.referrals.assign_me');
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
Route::post('/login', [AuthController::class, 'verifyLoginOtp'])->name('login.verify');
Route::post('/register', [AuthController::class, 'register'])->name('register');
//Route::post('/login', [AuthController::class, 'register'])->name('register');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');


Route::get('/check-login', function () {
    dd(Auth::user());
    return Auth::check()
        ? '👤 Logged in as: ' . Auth::user()->phone
        : '❌ Not logged in';
});


Route::get('/fake-login', function () {
    $user = User::first();
    Auth::guard('web')->login($user);
    session()->regenerate();
    return '✅ Logged in as: ' . $user->name;
});
