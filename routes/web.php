<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'throttle:60,1', \App\Http\Middleware\CheckAccountStatus::class])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Search & Reporting Engine
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Queues and Files
    Route::get('/queues/outgoing', [\App\Http\Controllers\QueueController::class, 'outgoing'])->name('queues.outgoing');
    Route::get('/queues/incoming', [\App\Http\Controllers\QueueController::class, 'incoming'])->name('queues.incoming');
    Route::get('/queues/pending', [\App\Http\Controllers\QueueController::class, 'pending'])->name('queues.pending');
    Route::get('/files/generate', [\App\Http\Controllers\FileGenerationController::class, 'create'])->name('files.create');
    Route::post('/files/generate', [\App\Http\Controllers\FileGenerationController::class, 'store'])->name('files.store');
    Route::get('/files/{file}', [\App\Http\Controllers\FileRecordController::class, 'show'])->name('files.show');
    Route::put('/files/{file}', [\App\Http\Controllers\FileRecordController::class, 'update'])->name('files.update');
    
    // File Movements (Dispatch, Receive, Return)
    Route::get('/files/{file}/dispatch', [\App\Http\Controllers\FileMovementController::class, 'createDispatch'])->name('files.dispatch.create');
    Route::post('/files/{file}/dispatch', [\App\Http\Controllers\FileMovementController::class, 'storeDispatch'])->name('files.dispatch.store');
    Route::post('/movements/{movement}/receive', [\App\Http\Controllers\FileMovementController::class, 'receive'])->name('movements.receive');
    Route::post('/movements/{movement}/reject', [\App\Http\Controllers\FileMovementController::class, 'reject'])->name('movements.reject');

    // Phase 12: Digital Document Endpoints
    Route::post('/files/{file}/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/{document}/version', [\App\Http\Controllers\DocumentController::class, 'updateVersion'])->name('documents.update-version');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');

    // Administration & Settings
    Route::prefix('admin')->name('admin.')->group(function () {
        // Departments
        Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [\App\Http\Controllers\DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}/edit', [\App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update');

        // Users
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');

        // System Settings
        Route::get('/settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/basic', [\App\Http\Controllers\SystemSettingController::class, 'updateBasic'])->name('settings.basic');
        Route::post('/settings/logo', [\App\Http\Controllers\SystemSettingController::class, 'updateLogo'])->name('settings.logo');
        Route::post('/settings/digital-module', [\App\Http\Controllers\SystemSettingController::class, 'updateDigitalModule'])->name('settings.digital');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
