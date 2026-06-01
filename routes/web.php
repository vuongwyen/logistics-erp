<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebitNoteController;
use App\Http\Controllers\DispatchOrderController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FieldAssignmentController;
use App\Http\Controllers\FieldStaffController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServicePriceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShippingJobController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports (ADMIN, ACCOUNTANT, DISPATCH)
    Route::middleware('role:ADMIN,ACCOUNTANT,DISPATCH')->group(function () {
        Route::get('/reports/operational', [ReportController::class, 'operational'])->name('reports.operational');
    });

    // Financial Reports (ADMIN, ACCOUNTANT only)
    Route::middleware('role:ADMIN,ACCOUNTANT')->group(function () {
        Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    });

    // System Settings & Backup (ADMIN / GĐ)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');

    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
        Route::get('/settings/backups/{filename}/download', [SettingController::class, 'downloadBackup'])->name('settings.backup-download');
        Route::delete('/settings/backups/{filename}', [SettingController::class, 'deleteBackup'])->name('settings.backup-delete');
        Route::post('/settings/installer/build', [SettingController::class, 'buildExeInstaller'])->name('settings.installer-build');
        Route::get('/settings/installer/download', [SettingController::class, 'downloadExeInstaller'])->name('settings.installer-download');
        Route::get('/settings/setup/download', function () {
            $path = public_path('NT-Logistics-ERP-Setup-v3.0.0.exe');
            if (!file_exists($path)) {
                abort(404, 'File not found');
            }
            return response()->download($path);
        })->name('settings.setup-download');
        Route::post('/settings/installer/sync', [SettingController::class, 'syncInstallers'])->name('settings.installer-sync');
        Route::delete('/settings/installer', [SettingController::class, 'deleteExeInstaller'])->name('settings.installer-delete');
        Route::post('/settings/restore', [SettingController::class, 'restore'])->name('settings.restore');
        Route::post('/settings/upload-asset', [SettingController::class, 'uploadAsset'])->name('settings.upload-asset');
        Route::get('/settings/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // User Management (ADMIN, DISPATCH)
    Route::middleware('role:ADMIN,DISPATCH')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Master Data - Customers (ADMIN, SALES)
    Route::middleware('role:ADMIN,SALES')->group(function () {
        Route::resource('customers', CustomerController::class)->except(['show']);
    });

    // Master Data - Logistics Resources (ADMIN, DISPATCH)
    Route::middleware('role:ADMIN,DISPATCH')->group(function () {
        Route::resource('locations', LocationController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('vehicles', VehicleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('drivers', DriverController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('field-staff', FieldStaffController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Master Data - Service Prices (ADMIN, ACCOUNTANT, SALES)
    Route::middleware('role:ADMIN,ACCOUNTANT,SALES')->group(function () {
        Route::resource('service-prices', ServicePriceController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Shipping Jobs (All roles can view, only SALES/ADMIN can create/edit)
    Route::get('shipping-jobs', [ShippingJobController::class, 'index'])->name('shipping-jobs.index');

    Route::middleware('role:ADMIN,SALES')->group(function () {
        Route::get('shipping-jobs/create', [ShippingJobController::class, 'create'])->name('shipping-jobs.create');
        Route::post('shipping-jobs', [ShippingJobController::class, 'store'])->name('shipping-jobs.store');
        Route::get('shipping-jobs/{shipping_job}/edit', [ShippingJobController::class, 'edit'])->name('shipping-jobs.edit');
        Route::put('shipping-jobs/{shipping_job}', [ShippingJobController::class, 'update'])->name('shipping-jobs.update');
        Route::delete('shipping-jobs/{shipping_job}', [ShippingJobController::class, 'destroy'])->name('shipping-jobs.destroy');
    });

    Route::get('shipping-jobs/{shipping_job}', [ShippingJobController::class, 'show'])->name('shipping-jobs.show');

    // Dispatch Orders
    // ADMIN and DISPATCH can do everything
    Route::middleware('role:ADMIN,DISPATCH,FIELD')->group(function () {
        Route::get('field-assignments', [FieldAssignmentController::class, 'index'])->name('field-assignments.index');
        Route::patch('field-assignments/{fieldAssignment}/status', [FieldAssignmentController::class, 'updateStatus'])->name('field-assignments.update-status');
    });

    Route::middleware('role:ADMIN,DISPATCH')->group(function () {
        Route::resource('dispatch-orders', DispatchOrderController::class)->only(['create', 'store', 'destroy']);
        Route::get('field-assignments/create', [FieldAssignmentController::class, 'create'])->name('field-assignments.create');
        Route::post('field-assignments', [FieldAssignmentController::class, 'store'])->name('field-assignments.store');
    });

    // DRIVER, ADMIN, DISPATCH can view and update status
    Route::middleware('role:ADMIN,DISPATCH,DRIVER')->group(function () {
        Route::get('dispatch-orders', [DispatchOrderController::class, 'index'])->name('dispatch-orders.index');
        Route::get('dispatch-orders/{dispatch_order}', [DispatchOrderController::class, 'show'])->name('dispatch-orders.show');
        Route::patch('/dispatch-orders/{dispatch_order}/status', [DispatchOrderController::class, 'updateStatus'])->name('dispatch-orders.update-status');
    });

    Route::middleware('role:ADMIN,ACCOUNTANT')->group(function () {
        Route::post('/dispatch-orders/{dispatch_order}/approve', [DispatchOrderController::class, 'approve'])->name('dispatch-orders.approve');
        Route::post('/dispatch-orders/{dispatch_order}/reject', [DispatchOrderController::class, 'reject'])->name('dispatch-orders.reject');
    });

    // Expenses & Documents (DRIVER cannot add)
    Route::middleware('role:ADMIN,ACCOUNTANT,SALES,DISPATCH,FIELD,DOCUMENT')->group(function () {
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    });

    Route::middleware('role:ADMIN,ACCOUNTANT,DOCUMENT')->group(function () {
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    // Finance - Cash Advances (FIELD/DISPATCH creates, ACCOUNTANT/ADMIN approves)
    Route::post('cash-advances', [CashAdvanceController::class, 'store'])->name('cash-advances.store');
    Route::middleware('role:ADMIN,ACCOUNTANT')->group(function () {
        Route::post('cash-advances/{cashAdvance}/approve', [CashAdvanceController::class, 'approve'])->name('cash-advances.approve');
    });

    // Finance - Billing & Payments (ADMIN, ACCOUNTANT)
    Route::middleware('role:ADMIN,ACCOUNTANT')->group(function () {
        Route::post('debit-notes', [DebitNoteController::class, 'store'])->name('debit-notes.store');
        Route::get('debit-notes/{debitNote}', [DebitNoteController::class, 'show'])->name('debit-notes.show');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('recurring-expenses', [RecurringExpenseController::class, 'store'])->name('recurring-expenses.store');
        Route::get('recurring-expenses/{recurringExpense}/edit', [RecurringExpenseController::class, 'edit'])->name('recurring-expenses.edit');
        Route::put('recurring-expenses/{recurringExpense}', [RecurringExpenseController::class, 'update'])->name('recurring-expenses.update');
        Route::delete('recurring-expenses/{recurringExpense}', [RecurringExpenseController::class, 'destroy'])->name('recurring-expenses.destroy');
    });

    // Profile Settings (All users)
    Route::get('/profile', function () {
        return redirect()->route('settings.index');
    })->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
