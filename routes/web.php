<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PulseController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;

// Root
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->dashboardRoute());
    }
    return redirect()->route('login');
});

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Employee ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
        Route::get('/history',   [EmployeeController::class, 'history'])->name('history');
        Route::get('/pulse/create', [PulseController::class, 'create'])->name('pulse.create');
        Route::post('/pulse',       [PulseController::class, 'store'])->name('pulse.store');
        Route::post('/timer/start',  [TimerController::class, 'start'])->name('timer.start');
        Route::post('/timer/stop',   [TimerController::class, 'stop'])->name('timer.stop');
        Route::post('/timer/pause',  [TimerController::class, 'pause'])->name('timer.pause');
        Route::post('/timer/resume', [TimerController::class, 'resume'])->name('timer.resume');
        Route::post('/timer/request-stop', [TimerController::class, 'requestStop'])->name('timer.request-stop');
        Route::get('/timer/status',  [TimerController::class, 'status'])->name('timer.status');

    });

// ─── Manager ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:manager,admin'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/pulses',    [PulseController::class, 'index'])->name('pulses');
        Route::post('/pulses/{pulse}/approve', [PulseController::class, 'approve'])->name('pulses.approve');
        Route::post('/pulses/{pulse}/reject',  [PulseController::class, 'reject'])->name('pulses.reject');
        Route::delete('/pulses/{pulse}',       [PulseController::class, 'destroy'])->name('pulses.destroy');
        Route::get('/team',     [ManagerController::class, 'team'])->name('team');
        Route::get('/reports',  [ReportController::class, 'index'])->name('reports');
        Route::post('/reports/generate',    [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/export/csv',   [ReportController::class, 'exportCsv'])->name('reports.csv');
        Route::get('/reports/export/pdf',   [ReportController::class, 'exportPdf'])->name('reports.pdf');
        
        // Managers can also add employees
        Route::get('/employees/create', [AdminController::class, 'createUser'])->name('employees.create');
        Route::post('/employees',        [AdminController::class, 'storeEmployee'])->name('employees.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}',      [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}',   [ManagerController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/users/{user}/reset-timer', [TimerController::class, 'forceStop'])->name('users.reset-timer');
    });




// ─── Admin ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard',          [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users',              [AdminController::class, 'users'])->name('users');
        Route::get('/users/create',       [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users',             [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit',  [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}',       [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}',    [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
        Route::get('/activity',           [AdminController::class, 'activityLogs'])->name('activity');
        Route::delete('/activity/clear',  [AdminController::class, 'clearActivityLogs'])->name('activity.clear');
        Route::get('/activity/export/csv', [AdminController::class, 'exportActivityCsv'])->name('activity.csv');
        Route::get('/activity/export/pdf', [AdminController::class, 'exportActivityPdf'])->name('activity.pdf');
    });

// ─── Notifications (AJAX polling) ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/notifications',             [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count',       [NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/read-all',   [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::delete('/notifications/clear-all',[NotificationController::class, 'clearAll'])->name('notifications.clearAll');
    Route::get('/notifications/{id}/read',   [NotificationController::class, 'markRead'])->name('notifications.read');
});
