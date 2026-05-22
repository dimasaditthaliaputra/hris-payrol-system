<?php

namespace App\Services;

use App\Repositories\Contracts\ThrPayrollRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ThrService
{
    public function __construct(
        protected ThrPayrollRepositoryInterface $thrRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function generateThr(int $year)
    {
        try {
            DB::beginTransaction();
            
            $employees = $this->employeeRepository->model()->whereNull('deleted_at')->get();
            $generatedCount = 0;

            foreach ($employees as $employee) {
                if ($this->thrRepository->checkExists($employee->id, $year)) {
                    continue;
                }

                if (!$employee->join_date || !$employee->basic_salary) {
                    continue; // Skip if missing essential data
                }

                $joinDate = Carbon::parse($employee->join_date);
                $endDate = Carbon::create($year, 12, 31); // Consider end of year or Hari Raya date? Standard practice is end of year or a specific cutoff date. Let's use end of year for calculation.
                
                $monthsOfService = $joinDate->diffInMonths($endDate);
                
                if ($monthsOfService < 1) {
                    continue; // Kurang dari 1 bulan tidak dapat THR
                }

                $multiplier = $monthsOfService >= 12 ? 1 : ($monthsOfService / 12);
                $thrAmount = $employee->basic_salary * $multiplier;

                $this->thrRepository->create([
                    'employee_id' => $employee->id,
                    'period_year' => $year,
                    'basic_salary' => $employee->basic_salary,
                    'length_of_service' => $monthsOfService,
                    'amount' => $thrAmount,
                    'status' => 'pending'
                ]);

                $generatedCount++;
            }

            DB::commit();

            $this->activityLogService->log('generate_thr', "Generate THR untuk tahun {$year}. Total: {$generatedCount} karyawan.");

            return [
                'success' => true,
                'message' => "Berhasil men-generate THR untuk {$generatedCount} karyawan."
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
