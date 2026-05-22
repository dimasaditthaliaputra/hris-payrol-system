<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOvertimeRequest;
use App\Http\Requests\UpdateOvertimeRequest;
use App\Repositories\Contracts\OvertimeRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Services\OvertimeService;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class OvertimeController extends Controller
{
    public function __construct(
        protected OvertimeRepositoryInterface $overtimeRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected OvertimeService $overtimeService,
        protected ActivityLogService $activityLogService
    ) {}

    public function index()
    {
        $employees = $this->employeeRepository->all();
        return view('overtimes.index', compact('employees'));
    }

    public function data()
    {
        $overtimes = $this->overtimeRepository->model()->with('employee')->latest('date');

        return DataTables::of($overtimes)
            ->addIndexColumn()
            ->addColumn('employee_nik', function ($row) {
                return $row->employee ? $row->employee->nik : '-';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? $row->employee->name : '-';
            })
            ->addColumn('amount_formatted', function ($row) {
                return 'Rp ' . number_format((float)$row->amount, 0, ',', '.');
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-employee_id="' . $row->employee_id . '" data-date="' . $row->date . '" data-duration_hours="' . $row->duration_hours . '"><i class="fas fa-edit"></i> Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StoreOvertimeRequest $request)
    {
        if ($this->overtimeService->isLocked($request->date)) {
            return response()->json(['success' => false, 'message' => 'Periode sudah dikunci (Payroll).'], 403);
        }

        $data = $request->validated();
        $data['amount'] = $this->overtimeService->calculateAmount((int)$data['employee_id'], (float)$data['duration_hours']);

        $overtime = $this->overtimeRepository->create($data);
        $this->activityLogService->log('create_overtime', 'Menambahkan lembur untuk ID: ' . $overtime->employee_id);
        
        return response()->json(['success' => true, 'message' => 'Lembur berhasil ditambahkan.']);
    }

    public function update(UpdateOvertimeRequest $request, string $id)
    {
        if ($this->overtimeService->isLocked($request->date)) {
            return response()->json(['success' => false, 'message' => 'Periode sudah dikunci (Payroll).'], 403);
        }

        $data = $request->validated();
        $data['amount'] = $this->overtimeService->calculateAmount((int)$data['employee_id'], (float)$data['duration_hours']);

        $this->overtimeRepository->update($id, $data);
        $this->activityLogService->log('update_overtime', 'Memperbarui lembur ID: ' . $id);

        return response()->json(['success' => true, 'message' => 'Lembur berhasil diperbarui.']);
    }

    public function destroy(string $id)
    {
        $overtime = $this->overtimeRepository->findById((int)$id);
        if ($overtime && $this->overtimeService->isLocked($overtime->date)) {
            return response()->json(['success' => false, 'message' => 'Periode sudah dikunci (Payroll).'], 403);
        }

        $this->overtimeRepository->delete($id);
        $this->activityLogService->log('delete_overtime', 'Menghapus lembur ID: ' . $id);

        return response()->json(['success' => true, 'message' => 'Lembur berhasil dihapus.']);
    }
}
