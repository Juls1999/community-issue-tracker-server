<?php

use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\IssueCategoryController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\IssueCommentController;
use App\Http\Controllers\Api\IssueAttachmentController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'index']);

// PUBLIC API
Route::get('/barangays', [BarangayController::class, 'index']);

// PRIVATE API
Route::middleware(['auth:sanctum'])->group(function () {
    // retrieves issues
    Route::get('/issues', [IssueController::class, 'index']);
    // creates issue
    Route::post('/issues', [IssueController::class, 'store']);
    // retrieves specific issue
    Route::get('/issues/{issue}', [IssueController::class, 'show']);
    // modifies issue
    Route::patch('/issues/{issue}', [IssueController::class, 'update']);
    // assigns staff to an issue
    Route::patch('/issues/{issue}/assign', [
        IssueController::class,
        'assign',
    ]);
    // updates issue's status
    Route::patch('/issues/{issue}/status', [
        IssueController::class,
        'updateStatus',
    ]);


    Route::get('/issues/{issue}/comments', [IssueCommentController::class, 'index']);

    Route::post('/issues/{issue}/comments', [IssueCommentController::class, 'store']);

    Route::get('/issues/{issue}/attachments', [IssueAttachmentController::class, 'index']);

    Route::post('/issues/{issue}/attachments', [IssueAttachmentController::class, 'store']);

    Route::get('/issues/{issue}/attachments/{attachment}/download', [IssueAttachmentController::class, 'download']);


    Route::get('/issue-categories', [
        IssueCategoryController::class,
        'index',
    ]);


    // PROFILE
    Route::patch('/user', [ProfileController::class, 'update']);

    Route::put('/user/password', [
        ProfileController::class,
        'updatePassword',
    ]);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::patch(
        '/notifications/{notification}/read',
        [NotificationController::class, 'markAsRead']
    );

    Route::patch(
        '/notifications/read-all',
        [NotificationController::class, 'markAllAsRead']
    );

    Route::delete('/notifications/{notifications}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/read', [NotificationController::class, 'destroyRead']);



});


Route::middleware([
    'auth:sanctum',
    'role:admin',
])->group(function () {
    // Administrator-only endpoints
    Route::get('/test', [IssueController::class, 'index']);
});

Route::middleware([
    'auth:sanctum',
    'role:staff,admin',
])->group(function () {
    // Staff and admin endpoints
});

Route::middleware([
    'auth:sanctum',
    'role:resident',
])->group(function () {
    // Resident-only endpoints
});