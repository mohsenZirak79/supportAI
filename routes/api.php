<?php

//use App\Domains\Auth\Controllers\AuthController;
use App\Domains\UserPanel\Controllers\TicketController;
use Illuminate\Http\Request;
use App\Services\FileUploadService;
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

//use App\Domains\UserPanel\Controllers\FileController;
//use App\Domains\AgentPanel\Controllers\TicketController;
//use App\Domains\AdminPanel\Controllers\UserController;
//use App\Domains\AdminPanel\Controllers\RoleController;

Route::prefix('v1')->middleware(['forceTestUser'])->group(function () {
    // Auth Routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/activate', [AuthController::class, 'activate']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-login-otp', [AuthController::class, 'verifyLoginOtp']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::patch('conversations/{conversation}/title', [ConversationController::class, 'updateTitle']);
    Route::delete('conversations/{conversation}', [ConversationController::class, 'destroy']);
    Route::post('conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']);

//    Route::get('tickets', [TicketController::class, 'index']);
//    Route::post('conversations/{conversation}/handoff', [TicketController::class, 'handoff']);
//    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
//    Route::patch('tickets/{ticket}', [TicketController::class, 'update']);
//    Route::post('tickets/{ticket}/messages', [TicketController::class, 'sendMessage']);
    // User Panel

    Route::get('tickets', [\App\Domains\UserPanel\Controllers\TicketController::class, 'index']); // لیست تیکت‌های اصلی کاربر
    Route::post('tickets', [\App\Domains\UserPanel\Controllers\TicketController::class, 'store']); // ایجاد تیکت جدید
    Route::get('tickets/{rootId}', [\App\Domains\UserPanel\Controllers\TicketController::class, 'show']); // مشاهده تمام رفت‌و‌برگشت‌ها
    Route::post('tickets/{rootId}/messages', [\App\Domains\UserPanel\Controllers\TicketController::class, 'sendMessage']); // پاسخ کاربر
//    Route::get('tickets_departments', [\App\Domains\UserPanel\Controllers\TicketController::class, 'getDepartments']);
    Route::get('agent/tickets', [AgentPanel\TicketController::class, 'index']); // لیست همه تیکت‌ها (فیلتر شده بر اساس نقش)
    Route::get('agent/tickets/{rootId}', [AgentPanel\TicketController::class, 'show']); // مشاهده رفت‌و‌برگشت‌ها
    Route::post('agent/tickets/{rootId}/messages', [AgentPanel\TicketController::class, 'sendMessage']); // پاسخ پشتیبان

    Route::post('files', function (Request $request, FileUploadService $uploadService) {
        $file = $request->file('file');
        $collection = $request->input('collection', 'default');
        $media = $uploadService->upload($file, $collection);
        return response()->json([
            'file_id' => $media->id,
            'url' => $media->getUrl(),
            'mime' => $media->mime_type,
            'size' => $media->size
        ]);
    });
    Route::post('files/{fileId}/finalize', function (Request $request, string $fileId, FileUploadService $uploadService) {
        $metadata = $request->input('metadata', []);
        $media = $uploadService->finalize($fileId, $metadata);
        return response()->json($media);
    });

    Route::get('support-roles', [\App\Domains\Shared\Controllers\RoleController::class, 'getSupportRoles']);
});
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
