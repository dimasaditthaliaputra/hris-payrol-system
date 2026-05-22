<?php

namespace App\Services;

use App\Repositories\Contracts\CashAdvanceRepositoryInterface;
use App\Repositories\Contracts\CashAdvanceInstallmentRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CashAdvanceService
{
    public function __construct(
        protected CashAdvanceRepositoryInterface $cashAdvanceRepository,
        protected CashAdvanceInstallmentRepositoryInterface $installmentRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function create(array $data)
    {
        $cashAdvance = $this->cashAdvanceRepository->create($data);
        $this->activityLogService->log('create_cash_advance', "Mengajukan kasbon baru ID: {$cashAdvance->id} sejumlah {$data['amount']}.");
        return $cashAdvance;
    }

    public function update(int $id, array $data)
    {
        $cashAdvance = $this->cashAdvanceRepository->findById($id);
        
        if ($cashAdvance->status === 'approved') {
            throw new Exception('Kasbon yang sudah disetujui tidak dapat diubah nilainya.');
        }

        $this->cashAdvanceRepository->update($id, $data);
        $this->activityLogService->log('update_cash_advance', "Memperbarui pengajuan kasbon ID: {$id}.");
        return true;
    }

    public function delete(int $id)
    {
        $cashAdvance = $this->cashAdvanceRepository->findById($id);
        
        if ($cashAdvance->status === 'approved' || $cashAdvance->status === 'paid_off') {
            throw new Exception('Kasbon yang sudah disetujui atau lunas tidak dapat dihapus.');
        }

        $this->cashAdvanceRepository->delete($id);
        $this->activityLogService->log('delete_cash_advance', "Menghapus pengajuan kasbon ID: {$id}.");
        return true;
    }

    public function approve(int $id, string $approvalDate)
    {
        try {
            DB::beginTransaction();
            
            $cashAdvance = $this->cashAdvanceRepository->findById($id);

            if ($cashAdvance->status !== 'pending') {
                throw new Exception('Hanya kasbon dengan status pending yang dapat disetujui.');
            }

            // Update status and approval date
            $this->cashAdvanceRepository->update($id, [
                'status' => 'approved',
                'approval_date' => $approvalDate
            ]);

            // Generate installments
            $startDate = Carbon::parse($approvalDate)->addMonth(); // Next month
            $installmentAmount = $cashAdvance->amount / $cashAdvance->tenor_months;

            for ($i = 0; $i < $cashAdvance->tenor_months; $i++) {
                $this->installmentRepository->create([
                    'cash_advance_id' => $id,
                    'month' => $startDate->month,
                    'year' => $startDate->year,
                    'amount' => $installmentAmount,
                    'status' => 'unpaid'
                ]);
                $startDate->addMonth(); // Move to next month for next installment
            }

            $this->activityLogService->log('approve_cash_advance', "Menyetujui kasbon ID: {$id} dan meng-generate {$cashAdvance->tenor_months} cicilan.");

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(int $id)
    {
        $cashAdvance = $this->cashAdvanceRepository->findById($id);

        if ($cashAdvance->status !== 'pending') {
            throw new Exception('Hanya kasbon dengan status pending yang dapat ditolak.');
        }

        $this->cashAdvanceRepository->update($id, [
            'status' => 'rejected',
            'approval_date' => now()
        ]);

        $this->activityLogService->log('reject_cash_advance', "Menolak kasbon ID: {$id}.");
        return true;
    }
}
