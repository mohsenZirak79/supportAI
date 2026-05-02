<?php

use App\Domains\AdminPanel\Controllers\AdminChatController;
use App\Domains\AdminPanel\Controllers\AdminTicketController;
use App\Domains\AdminPanel\Controllers\AdminWidgetCallbackController;
use App\Domains\Auth\Controllers\WebController;
use App\Domains\Shared\Controllers\UserController;
use App\Domains\Shared\Controllers\NotificationController;
use App\Domains\AdminPanel\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use App\Http\Middleware\CheckPermissionForRoute;
use App\Http\Middleware\EnsureCanAccessAdminWeb;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Domains\Role\Controllers\RoleController;

use App\Domains\Auth\Controllers\AuthController;

// Landing page - redirects authenticated users to dashboard
Route::get('/', [\App\Domains\Auth\Controllers\LandingController::class, 'index'])->name('landing');

// Landing page auth routes (email/password with remember me)
Route::post('/landing/login', [\App\Domains\Auth\Controllers\LandingController::class, 'login'])->name('landing.login');
Route::post('/landing/register', [\App\Domains\Auth\Controllers\LandingController::class, 'register'])->name('landing.register');

Route::get('/chat', fn () => View::make('chat.index'))->name('chat')->middleware('ensure.jwt.cookie');
Route::get('/ticket', fn () => View::make('tickets.index'))->middleware('ensure.jwt.cookie');
Route::get('/user/profile', [UserProfileController::class, 'index'])->name('user.profile')->middleware('ensure.jwt.cookie');

Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
Route::post('/activate', [AuthController::class, 'activate'])->name('activate');

Route::get('/test', function () {
    return view('user.index');
});

Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');

/** میان‌افزارهای مشترک مسیرهای وب پنل مدیریت + CRUD کاربر/نقش */
$adminWebStack = [
    'web',
    'auth:web',
    EnsureCanAccessAdminWeb::class,
    CheckPermissionForRoute::class,
];

Route::middleware($adminWebStack)
    ->prefix('admin')
    ->group(function () {
        Route::get('/users', [WebController::class, 'showUsers'])->name('admin.users');
        Route::get('/roles', [WebController::class, 'showRoles'])->name('admin.roles');
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets');
        Route::get('/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
        Route::post('/tickets/{id}/messages', [AdminTicketController::class, 'reply'])->name('admin.tickets.reply');
        Route::get('/chats', [AdminChatController::class, 'index'])->name('admin.chats');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('admin.notifications.read');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
        Route::get('dashboard', [\App\Domains\AdminPanel\Controllers\AdminDashboardController::class, '__invoke'])->name('admin.dashboard');
        Route::get('/chats/{conversation}/detail', [AdminChatController::class, 'detail'])->name('admin.chats.detail');
        Route::get('/callbacks', [AdminWidgetCallbackController::class, 'index'])->name('admin.callbacks');
        Route::patch('/callbacks/{widgetCallbackRequest}', [AdminWidgetCallbackController::class, 'update'])->name('admin.callbacks.update');
        Route::post('/referrals/{referral}/respond', [AdminChatController::class, 'respond'])->name('admin.referrals.respond');
        Route::post('/referrals/{referral}/assign-me', [AdminChatController::class, 'assignMe'])->name('admin.referrals.assign_me');
    });

Route::middleware($adminWebStack)
    ->prefix('user')
    ->name('users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

Route::middleware($adminWebStack)
    ->prefix('role')
    ->name('roles.')
    ->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

Route::post('/login', [AuthController::class, 'verifyLoginOtp'])->name('login.verify');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/** پروفایل قدیمی Blade ادمین؛ فقط کارمندان پنل (مثل بقیهٔ admin) */
Route::middleware(['web', 'auth:web', EnsureCanAccessAdminWeb::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
