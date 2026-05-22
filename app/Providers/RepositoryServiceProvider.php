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

        // TODO: Tambahkan binding berikut seiring modul dikembangkan:
        // $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        // $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        // $this->app->bind(PayrollRepositoryInterface::class, PayrollRepository::class);
        // $this->app->bind(CashAdvanceRepositoryInterface::class, CashAdvanceRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
