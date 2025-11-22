<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\AssetDetailController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public asset detail page (for QR Code scanning)
Route::get('/detail', [AssetDetailController::class, 'show'])->name('asset.detail');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/stats', [DashboardController::class, 'getStats'])->name('api.stats');

    // Assets Management
    Route::middleware(['level:assets-inv'])->group(function () {
        Route::resource('assets-inv', AssetController::class);
        Route::post('/assets-inv/generate-qrcode', [AssetController::class, 'generateQrCode'])->name('assets.generate-qrcode');
        Route::post('/assets-inv/bulk-delete', [AssetController::class, 'bulkDelete'])->name('assets.bulk-delete');
        Route::post('/assets-inv/export', [AssetController::class, 'export'])->name('assets.export');
    });

    // Borrowing Management
    Route::middleware(['level:borrowing'])->group(function () {
        Route::resource('borrowings', BorrowingController::class)->except(['edit', 'update']);
        Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnAsset'])->name('borrowings.return');
    });

    // Maintenance Management
    Route::middleware(['level:maintenance'])->group(function () {
        Route::resource('maintenances', MaintenanceController::class)->except(['edit', 'update']);
    });

    // User Management
    Route::middleware(['level:users'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Asset Requests Management
    Route::middleware(['level:requests'])->group(function () {
        Route::resource('requests', AssetRequestController::class)->except(['edit', 'update']);
        Route::post('/requests/{assetRequest}/approve', [AssetRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{assetRequest}/reject', [AssetRequestController::class, 'reject'])->name('requests.reject');
    });

    // Reports
    Route::middleware(['level:reports'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // QR Code Management
    Route::middleware(['level:qrCode'])->group(function () {
        Route::resource('qrcodes', QrCodeController::class)->only(['index', 'show', 'destroy']);
        Route::post('/qrcodes/{qrCode}/toggle-status', [QrCodeController::class, 'toggleStatus'])->name('qrcodes.toggle-status');
        Route::get('/qrcodes/{qrCode}/print', [QrCodeController::class, 'print'])->name('qrcodes.print');
        Route::get('/qrcodes/export/pdf', [QrCodeController::class, 'exportAllPdf'])->name('qrcodes.export-pdf');
    });
});