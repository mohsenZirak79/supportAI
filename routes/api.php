<?php

//use App\Domains\Auth\Controllers\AuthController;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//
//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
//
//Route::post('/auth/activate', [AuthController::class, 'activate']);
//Route::post('/auth/verify-login-otp', [AuthController::class, 'verifyLoginOtp']);
//

//use App\Domains\Auth\Controllers\AuthController;
//use App\Domains\UserPanel\Controllers\ConversationController;
//use App\Domains\UserPanel\Controllers\FileController;
//use App\Domains\AgentPanel\Controllers\TicketController;
//use App\Domains\AdminPanel\Controllers\UserController;
//use App\Domains\AdminPanel\Controllers\RoleController;
//use App\Http\Middleware\IdleTimeout;
//use App\Http\Middleware\GuestChat;

use App\Domains\Auth\Controllers\AuthController;
use App\Domains\UserPanel\Controllers\ConversationController;
use App\Domains\UserPanel\Controllers\FileController;
use App\Domains\AgentPanel\Controllers\TicketController;
use App\Domains\AdminPanel\Controllers\UserController;
use App\Domains\AdminPanel\Controllers\RoleController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/activate', [AuthController::class, 'activate']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-login-otp', [AuthController::class, 'verifyLoginOtp']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // User Panel Routes
//    Route::middleware(['auth:sanctum', \App\Http\Middleware\GuestChat::class, \App\Http\Middleware\IdleTimeout::class])->group(function () {
//        Route::get('/conversations', [ConversationController::class, 'index']);
//        Route::get('/conversations/{id}/messages', [ConversationController::class, 'messages']);
//        Route::post('/conversations/{id}/messages', [ConversationController::class, 'sendMessage']);
//        Route::post('/conversations/{id}/handoff', [ConversationController::class, 'handoff']);
//        Route::post('/files', [FileController::class, 'store']);
//        Route::post('/files/{id}/finalize', [FileController::class, 'finalize']);
//    });
//
//    // Agent Panel Routes
//    Route::prefix('agent')->middleware(['auth:sanctum', 'role:support_agent'])->group(function () {
//        Route::get('/tickets', [TicketController::class, 'index']);
//        Route::post('/tickets/{id}/claim', [TicketController::class, 'claim']);
//    });
//
//    // Admin Panel Routes
//    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
//        Route::get('/users', [UserController::class, 'index']);
//        Route::patch('/users/{id}/roles', [UserController::class, 'updateRoles']);
//        Route::get('/roles', [RoleController::class, 'index']);
//        Route::post('/roles', [RoleController::class, 'store']);
//        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
//    });
});

//Route::prefix('v1')->group(function () {
    // Auth Routes
//    Route::post('/auth/register', [AuthController::class, 'register']);
//    Route::post('/auth/activate', [AuthController::class, 'activate']);
//    Route::post('/auth/login', [AuthController::class, 'login']);
//    Route::post('/auth/verify-login-otp', [AuthController::class, 'verifyLoginOtp']);
//    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // User Panel Routes
//    Route::middleware(['auth:sanctum', GuestChat::class, IdleTimeout::class])->group(function () {
//        Route::get('/conversations', [ConversationController::class, 'index']);
//        Route::get('/conversations/{id}/messages', [ConversationController::class, 'messages']);
//        Route::post('/conversations/{id}/messages', [ConversationController::class, 'sendMessage']);
//        Route::post('/conversations/{id}/handoff', [ConversationController::class, 'handoff']);
//        Route::post('/files', [FileController::class, 'store']);
//        Route::post('/files/{id}/finalize', [FileController::class, 'finalize']);
//    });
//
//    // Agent Panel Routes
//    Route::prefix('agent')->middleware(['auth:sanctum', 'role:support_agent'])->group(function () {
//        Route::get('/tickets', [TicketController::class, 'index']);
//        Route::post('/tickets/{id}/claim', [TicketController::class, 'claim']);
//    });
//
//    // Admin Panel Routes
//    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
//        Route::get('/users', [UserController::class, 'index']);
//        Route::patch('/users/{id}/roles', [UserController::class, 'updateRoles']);
//        Route::get('/roles', [RoleController::class, 'index']);
//        Route::post('/roles', [RoleController::class, 'store']);
//        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
//    });
//});
