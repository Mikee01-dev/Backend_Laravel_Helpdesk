<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/dashboard/stats', [TicketController::class, 'dashboard']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::get('/tickets/{id}/tracking', [TicketController::class, 'getTracking']);
    
    Route::get('/tickets/{ticketId}/comments', [CommentController::class, 'index']);
    Route::post('/tickets/{ticketId}/comments', [CommentController::class, 'store']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::middleware('role:user')->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']); 
    });

    Route::middleware('role:helpdesk,admin')->group(function () {
        Route::put('/tickets/{id}/status', [TicketController::class, 'updateStatus']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);
        Route::post('/categories', [CategoryController::class, 'store']);
    });

});