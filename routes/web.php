<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - 5758inc Finance Management System
| Strictly NO Public Registration
|--------------------------------------------------------------------------
*/

// Authentication & Two-Factor Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/2fa/setup', [AuthController::class, 'showTwoFactorSetup'])->name('2fa.setup');
    Route::post('/2fa/setup', [AuthController::class, 'twoFactorSetup']);

    Route::get('/2fa/verify', [AuthController::class, 'showTwoFactorVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [AuthController::class, 'twoFactorVerify']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Area: Requires User Login & Passed 2FA Verification
Route::middleware(['auth', '2fa'])->group(function () {
    // Root Redirect
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Module 1: Dasbor Eksekutif (Owner & Admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Module 2: Kalkulator Proyeksi & Auto-Termin Generator
    Route::get('/projections', [ProjectionController::class, 'index'])->name('projections.index');
    Route::get('/projections/create', [ProjectionController::class, 'create'])->name('projections.create');
    Route::post('/projections', [ProjectionController::class, 'store'])->name('projections.store');
    Route::post('/projections/{project}/execute', [ProjectionController::class, 'execute'])->name('projections.execute');

    // Module 4: Pencatatan Arus Kas & Manajemen Termin
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/{transaction}/mark-as-paid', [TransactionController::class, 'markAsPaid'])->name('transactions.mark-as-paid');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Module 3: Master Data Management
    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::post('/accounts/{account}/recalculate', [AccountController::class, 'recalculate'])->name('accounts.recalculate');

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('projects', ProjectController::class);

    // Module 5: Report Generator (Laporan Keseluruhan)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    // Owner Only: User & Security Management (Reset 2FA)
    Route::middleware('role:owner')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');
    });
});
