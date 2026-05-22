<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Fallback Dashboard Route (Untuk Breeze Components)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isSuperAdmin()) return redirect()->route('admin.dashboard');
        if ($user->isHrd()) return redirect()->route('hrd.dashboard');
        if ($user->isFinance()) return redirect()->route('finance.dashboard');
        return redirect()->route('karyawan.dashboard');
    })->name('dashboard');
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Super Admin Routes
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('dashboard');
        })->name('admin.dashboard');

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

        // Allowance Types
        Route::resource('allowance_types', \App\Http\Controllers\AllowanceTypeController::class)->except(['create', 'show', 'edit']);
        Route::get('allowance_types/data', [\App\Http\Controllers\AllowanceTypeController::class, 'data'])->name('allowance_types.data');

        // Deduction Types
        Route::resource('deduction_types', \App\Http\Controllers\DeductionTypeController::class)->except(['create', 'show', 'edit']);
        Route::get('deduction_types/data', [\App\Http\Controllers\DeductionTypeController::class, 'data'])->name('deduction_types.data');
    });

    // HRD Routes
    Route::middleware(['role:hrd'])->group(function () {
        Route::get('/hrd/dashboard', function () {
            return view('dashboard');
        })->name('hrd.dashboard');
    });

    // Finance Routes
    Route::middleware(['role:finance'])->group(function () {
        Route::get('/finance/dashboard', function () {
            return view('dashboard');
        })->name('finance.dashboard');
    });

    // Karyawan Routes
    Route::middleware(['role:karyawan'])->group(function () {
        Route::get('/karyawan/dashboard', function () {
            return view('dashboard');
        })->name('karyawan.dashboard');
    });
});

require __DIR__.'/auth.php';
