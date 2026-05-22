<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\DepartmentRepository;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\PositionRepository;
use App\Repositories\Contracts\AllowanceTypeRepositoryInterface;
use App\Repositories\AllowanceTypeRepository;
use App\Repositories\Contracts\DeductionTypeRepositoryInterface;
use App\Repositories\DeductionTypeRepository;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\EmployeeRepository;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\AttendanceRepository;
use App\Repositories\Contracts\OvertimeRepositoryInterface;
use App\Repositories\OvertimeRepository;
use App\Repositories\Contracts\IncentiveRepositoryInterface;
use App\Repositories\IncentiveRepository;
use App\Repositories\Contracts\ThrPayrollRepositoryInterface;
use App\Repositories\ThrPayrollRepository;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Repositories\PayrollRepository;
use App\Repositories\Contracts\PayrollDetailRepositoryInterface;
use App\Repositories\PayrollDetailRepository;
use Illuminate\Support\ServiceProvider;

/**
 * RepositoryServiceProvider.
 *
 * Mendaftarkan semua binding antara Repository Interface dan implementasinya.
 * Ini adalah inti dari Repository Pattern — Controller/Service hanya
 * bergantung pada Interface, bukan concrete implementation.
 *
 * Setiap kali modul baru ditambahkan, daftarkan binding baru di sini.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Master Data Repositories
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(AllowanceTypeRepositoryInterface::class, AllowanceTypeRepository::class);
        $this->app->bind(DeductionTypeRepositoryInterface::class, DeductionTypeRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(OvertimeRepositoryInterface::class, OvertimeRepository::class);
        $this->app->bind(IncentiveRepositoryInterface::class, IncentiveRepository::class);
        $this->app->bind(ThrPayrollRepositoryInterface::class, ThrPayrollRepository::class);
        $this->app->bind(\App\Repositories\Contracts\CashAdvanceRepositoryInterface::class, \App\Repositories\CashAdvanceRepository::class);
        $this->app->bind(\App\Repositories\Contracts\CashAdvanceInstallmentRepositoryInterface::class, \App\Repositories\CashAdvanceInstallmentRepository::class);
        $this->app->bind(PayrollRepositoryInterface::class, PayrollRepository::class);
        $this->app->bind(PayrollDetailRepositoryInterface::class, PayrollDetailRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
