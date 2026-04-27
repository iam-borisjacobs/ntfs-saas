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
    Route::get('/queues/department-inbox', [\App\Http\Controllers\QueueController::class, 'departmentInbox'])->name('queues.department-inbox');
    Route::get('/documents/outgoing', [\App\Http\Controllers\OutgoingMonitorController::class, 'index'])->name('documents.outgoing');
    Route::post('/documents/{file}/alert', [\App\Http\Controllers\OutgoingMonitorController::class, 'sendAlert'])->name('documents.alert');
    Route::get('/queues/pending', [\App\Http\Controllers\QueueController::class, 'pending'])->name('queues.pending');
    Route::get('/files/generate', [\App\Http\Controllers\FileGenerationController::class, 'create'])->name('files.create');
    Route::post('/files/generate', [\App\Http\Controllers\FileGenerationController::class, 'store'])->name('files.store');
    Route::get('/files/{file}', [\App\Http\Controllers\FileRecordController::class, 'show'])->name('files.show');
    Route::put('/files/{file}', [\App\Http\Controllers\FileRecordController::class, 'update'])->name('files.update');
    
    // File Jackets
    Route::get('/registry/file-jackets', [\App\Http\Controllers\FileJacketController::class, 'index'])->name('file-jackets.index');
    Route::get('/registry/file-jackets/create', [\App\Http\Controllers\FileJacketController::class, 'create'])->name('file-jackets.create');
    Route::post('/registry/file-jackets', [\App\Http\Controllers\FileJacketController::class, 'store'])->name('file-jackets.store');
    Route::get('/registry/file-jackets/{jacket}', [\App\Http\Controllers\FileJacketController::class, 'show'])->name('file-jackets.show');
    Route::get('/registry/file-jackets/{jacket}/edit', [\App\Http\Controllers\FileJacketController::class, 'edit'])->name('file-jackets.edit');
    Route::put('/registry/file-jackets/{jacket}', [\App\Http\Controllers\FileJacketController::class, 'update'])->name('file-jackets.update');
    Route::post('/registry/file-jackets/{jacket}/close', [\App\Http\Controllers\FileJacketController::class, 'close'])->name('file-jackets.close');
    Route::post('/registry/file-jackets/{jacket}/archive', [\App\Http\Controllers\FileJacketController::class, 'archive'])->name('file-jackets.archive');
    Route::post('/registry/file-jackets/{jacket}/reactivate', [\App\Http\Controllers\FileJacketController::class, 'reactivate'])->name('file-jackets.reactivate');
    Route::post('/registry/file-jackets/{jacket}/file-document', [\App\Http\Controllers\FileJacketController::class, 'fileDocument'])->name('file-jackets.file-document');
    Route::post('/registry/file-jackets/{jacket}/unfile-document', [\App\Http\Controllers\FileJacketController::class, 'unfileDocument'])->name('file-jackets.unfile-document');

    // Jacket Movements (Dispatch / Receive)
    Route::get('/registry/file-jackets/{jacket}/dispatch', [\App\Http\Controllers\FileJacketMovementController::class, 'createDispatch'])->name('file-jackets.dispatch.create');
    Route::post('/registry/file-jackets/{jacket}/dispatch', [\App\Http\Controllers\FileJacketMovementController::class, 'storeDispatch'])->name('file-jackets.dispatch.store');
    Route::get('/registry/jacket-movements/{movement}/receive', [\App\Http\Controllers\FileJacketMovementController::class, 'showReceive'])->name('jacket-movements.receive.form');
    Route::post('/registry/jacket-movements/{movement}/receive', [\App\Http\Controllers\FileJacketMovementController::class, 'receive'])->name('jacket-movements.receive');

    // AJAX: Department Users (for dispatch dropdown)
    Route::get('/api/departments/{department}/users', function (\App\Models\Department $department) {
        return $department->users()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'system_identifier']);
    })->name('api.department.users');

    // File Movements (Dispatch, Receive, Return)
    Route::get('/files/{file}/dispatch', [\App\Http\Controllers\FileMovementController::class, 'createDispatch'])->name('files.dispatch.create');
    Route::post('/files/{file}/dispatch', [\App\Http\Controllers\FileMovementController::class, 'storeDispatch'])->name('files.dispatch.store');
    Route::get('/movements/{movement}/receive', [\App\Http\Controllers\FileMovementController::class, 'showReceive'])->name('movements.receive.form');
    Route::post('/movements/{movement}/receive', [\App\Http\Controllers\FileMovementController::class, 'receive'])->name('movements.receive');
    Route::post('/movements/{movement}/reject', [\App\Http\Controllers\FileMovementController::class, 'reject'])->name('movements.reject');
    Route::post('/movements/{movement}/close', [\App\Http\Controllers\FileMovementController::class, 'close'])->name('movements.close');

    // Phase 12: Digital Document Endpoints
    Route::post('/files/{file}/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/{document}/version', [\App\Http\Controllers\DocumentController::class, 'updateVersion'])->name('documents.update-version');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');

    // Reminders
    Route::get('/api/reminders', [\App\Http\Controllers\ReminderController::class, 'index'])->name('api.reminders.index');
    Route::post('/api/reminders', [\App\Http\Controllers\ReminderController::class, 'store'])->name('api.reminders.store');
    Route::put('/api/reminders/{reminder}/status', [\App\Http\Controllers\ReminderController::class, 'updateStatus'])->name('api.reminders.status');
    Route::delete('/api/reminders/{reminder}', [\App\Http\Controllers\ReminderController::class, 'destroy'])->name('api.reminders.destroy');

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
        Route::get('/users/bulk', [\App\Http\Controllers\UserController::class, 'bulk'])->name('users.bulk');
        Route::post('/users/bulk', [\App\Http\Controllers\UserController::class, 'processBulk'])->name('users.bulk.process');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');

        // System Settings
        Route::get('/settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/basic', [\App\Http\Controllers\SystemSettingController::class, 'updateBasic'])->name('settings.basic');
        Route::post('/settings/logo', [\App\Http\Controllers\SystemSettingController::class, 'updateLogo'])->name('settings.logo');
        Route::post('/settings/addon-toggles', [\App\Http\Controllers\SystemSettingController::class, 'updateAddonToggles'])->name('settings.addons');
        Route::get('/settings/terminology', [\App\Http\Controllers\SystemTerminologyController::class, 'index'])->name('settings.terminology');
        Route::put('/settings/terminology', [\App\Http\Controllers\SystemTerminologyController::class, 'update'])->name('settings.terminology.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
