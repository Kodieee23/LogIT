<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Defining constant to satisfy SonarQube (php:S1192)
const PROFILE_PATH = '/profile';

Route::get('/', function () {
    // Using Auth::check() instead of auth()->check() to satisfy Intelephense
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('home');

Route::post('/forgot-password-request', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'requestAdminReset'])->name('password.request.admin');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/task', [DashboardController::class, 'store'])->name('dashboard.task.store');
    Route::patch('/dashboard/task/{task}', [DashboardController::class, 'updateStatus'])->name('dashboard.task.update');
    Route::post('/dashboard/task/{task}/comment', [DashboardController::class, 'storeComment'])->name('dashboard.task.comment');
    Route::get('/api/notifications/unresolved', [DashboardController::class, 'unresolvedCount'])->name('api.notifications.unresolved');

    // Profile - Using the constant PROFILE_PATH
    Route::get(PROFILE_PATH, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch(PROFILE_PATH, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete(PROFILE_PATH, [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/admin/export/csv', [AdminController::class, 'exportCsv'])->name('admin.export.csv');
    Route::get('/admin/export/pdf', [AdminController::class, 'exportPdf'])->name('admin.export.pdf');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::patch('/admin/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('admin.users.reset_password');
    Route::post('/admin/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/admin/categories/{category}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');
});

require __DIR__.'/auth.php';
