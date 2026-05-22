<?php

namespace App\Services;

use App\Repositories\Contracts\IncentiveRepositoryInterface;
use Exception;

class IncentiveService
{
    public function __construct(
        protected IncentiveRepositoryInterface $incentiveRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function isLocked(string $date): bool
    {
        // TODO: Implementasi logika pengecekan periode payroll yang sudah di-lock.
        return false;
    }

    public function createIncentive(array $data)
    {
        if ($this->isLocked($data['date'])) {
            throw new Exception('Periode untuk tanggal ini sudah dikunci (Payroll).');
        }

        $incentive = $this->incentiveRepository->create($data);
        $this->activityLogService->log('create_incentive', "Menambahkan insentif untuk Karyawan ID: {$data['employee_id']} sebesar {$data['amount']}.");

        return $incentive;
    }

    public function updateIncentive(int $id, array $data)
    {
        $incentive = $this->incentiveRepository->findById($id);
        
        if ($this->isLocked($incentive->date) || (!empty($data['date']) && $this->isLocked($data['date']))) {
            throw new Exception('Periode untuk insentif ini sudah dikunci (Payroll).');
        }

        $this->incentiveRepository->update($id, $data);
        $this->activityLogService->log('update_incentive', "Memperbarui insentif ID: {$id}.");

        return true;
    }

    public function deleteIncentive(int $id)
    {
        $incentive = $this->incentiveRepository->findById($id);

        if ($this->isLocked($incentive->date)) {
            throw new Exception('Periode untuk insentif ini sudah dikunci (Payroll).');
        }

        $this->incentiveRepository->delete($id);
        $this->activityLogService->log('delete_incentive', "Menghapus insentif ID: {$id}.");

        return true;
    }
}
