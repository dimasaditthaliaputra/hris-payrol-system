<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Fallback Dashboard Route (Untuk Breeze Components)
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Super Admin Routes
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('admin.dashboard');

        // Roles Management
        Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);
        Route::get('roles/data', [RoleController::class, 'data'])->name('roles.data');

        // Users Management
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
        Route::get('users/data', [\App\Http\Controllers\UserController::class, 'data'])->name('users.data');
    });

    // Master Data (Bisa diakses Super Admin & HRD)
    Route::middleware(['role:super_admin,hrd'])->group(function () {
        // Departments
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['create', 'show', 'edit']);
        Route::get('departments/data', [\App\Http\Controllers\DepartmentController::class, 'data'])->name('departments.data');

        // Positions
        Route::resource('positions', \App\Http\Controllers\PositionController::class)->except(['create', 'show', 'edit']);
        Route::get('positions/data', [\App\Http\Controllers\PositionController::class, 'data'])->name('positions.data');

        // Employees
        Route::get('employees-data', [\App\Http\Controllers\EmployeeController::class, 'data'])->name('employees.data');
        Route::get('employees/download-template', [\App\Http\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.download-template');
        Route::post('employees/import', [\App\Http\Controllers\EmployeeController::class, 'importExcel'])->name('employees.import');
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class);

        // Attendances
        Route::get('attendances-data', [\App\Http\Controllers\AttendanceController::class, 'data'])->name('attendances.data');
        Route::get('attendances/download-template', [\App\Http\Controllers\AttendanceController::class, 'downloadTemplate'])->name('attendances.download-template');
        Route::post('attendances/import', [\App\Http\Controllers\AttendanceController::class, 'import'])->name('attendances.import');
        Route::resource('attendances', \App\Http\Controllers\AttendanceController::class)->except(['create', 'show', 'edit']);

        // Attendance Report
        Route::get('attendances-report', [\App\Http\Controllers\AttendanceReportController::class, 'index'])->name('attendances.report');
        Route::get('attendances-report-data', [\App\Http\Controllers\AttendanceReportController::class, 'data'])->name('attendances.report.data');

        // Overtimes
        Route::get('overtimes-data', [\App\Http\Controllers\OvertimeController::class, 'data'])->name('overtimes.data');
        Route::resource('overtimes', \App\Http\Controllers\OvertimeController::class)->except(['create', 'show', 'edit']);

        // Incentives
        Route::get('incentives-data', [\App\Http\Controllers\IncentiveController::class, 'data'])->name('incentives.data');
        Route::resource('incentives', \App\Http\Controllers\IncentiveController::class)->except(['create', 'show', 'edit']);

        // THR Payrolls
        Route::get('thr-payrolls-data', [\App\Http\Controllers\ThrPayrollController::class, 'data'])->name('thr_payrolls.data');
        Route::post('thr-payrolls/generate', [\App\Http\Controllers\ThrPayrollController::class, 'generate'])->name('thr_payrolls.generate');
        Route::get('thr-payrolls', [\App\Http\Controllers\ThrPayrollController::class, 'index'])->name('thr_payrolls.index');

        // Cash Advances (Kasbon)
        Route::get('cash-advances/data', [\App\Http\Controllers\CashAdvanceController::class, 'data'])->name('cash_advances.data');
        Route::post('cash-advances/{id}/approve', [\App\Http\Controllers\CashAdvanceController::class, 'approve'])->name('cash_advances.approve');
        Route::post('cash-advances/{id}/reject', [\App\Http\Controllers\CashAdvanceController::class, 'reject'])->name('cash_advances.reject');
        Route::resource('cash-advances', \App\Http\Controllers\CashAdvanceController::class)->names('cash_advances')->except(['create', 'edit']);

        // Allowance Types
        Route::resource('allowance_types', \App\Http\Controllers\AllowanceTypeController::class)->except(['create', 'show', 'edit']);
        Route::get('allowance_types/data', [\App\Http\Controllers\AllowanceTypeController::class, 'data'])->name('allowance_types.data');

        // Deduction Types
        Route::resource('deduction_types', \App\Http\Controllers\DeductionTypeController::class)->except(['create', 'show', 'edit']);
        Route::get('deduction_types/data', [\App\Http\Controllers\DeductionTypeController::class, 'data'])->name('deduction_types.data');

        // Payroll
        Route::get('payrolls/data', [\App\Http\Controllers\PayrollController::class, 'data'])->name('payrolls.data');
        Route::get('payrolls/{id}/detail-data', [\App\Http\Controllers\PayrollController::class, 'detailData'])->name('payrolls.detail.data');
        Route::post('payrolls/{id}/lock', [\App\Http\Controllers\PayrollController::class, 'lock'])->name('payrolls.lock');
        Route::post('payrolls/{id}/unlock', [\App\Http\Controllers\PayrollController::class, 'unlock'])->name('payrolls.unlock');
        Route::post('payrolls/{id}/approve', [\App\Http\Controllers\PayrollController::class, 'approve'])->name('payrolls.approve');
        Route::get('payrolls/slip/{detailId}', [\App\Http\Controllers\PayrollController::class, 'downloadSlip'])->name('payrolls.slip');
        Route::resource('payrolls', \App\Http\Controllers\PayrollController::class)->except(['create', 'edit']);

        // Reports
        Route::get('reports/payroll', [\App\Http\Controllers\ReportController::class, 'payrollIndex'])->name('reports.payroll');
        Route::post('reports/payroll/export', [\App\Http\Controllers\ReportController::class, 'exportPayrollExcel'])->name('reports.payroll.export');
    });

    // HRD Routes
    Route::middleware(['role:hrd'])->group(function () {
        Route::get('/hrd/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('hrd.dashboard');
    });

    // Finance Routes
    Route::middleware(['role:finance'])->group(function () {
        Route::get('/finance/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('finance.dashboard');
    });

    // Karyawan Routes
    Route::middleware(['role:karyawan'])->group(function () {
        Route::get('/karyawan/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('karyawan.dashboard');

        Route::get('/karyawan/payrolls', [\App\Http\Controllers\Karyawan\MyPayrollController::class, 'index'])->name('karyawan.payrolls.index');
        Route::get('/karyawan/payrolls/data', [\App\Http\Controllers\Karyawan\MyPayrollController::class, 'data'])->name('karyawan.payrolls.data');
    });
});

require __DIR__.'/auth.php';
