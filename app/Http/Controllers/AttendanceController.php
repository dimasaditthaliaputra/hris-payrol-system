<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Services\AttendanceService;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
        protected AttendanceService $attendanceService,
        protected ActivityLogService $activityLogService
    ) {}

    public function index()
    {
        $employees = $this->employeeRepository->all();
        return view('attendances.index', compact('employees'));
    }

    public function data()
    {
        $attendances = $this->attendanceRepository->model()->with('employee')->latest('date');

        return DataTables::of($attendances)
            ->addIndexColumn()
            ->addColumn('employee_nik', function ($row) {
                return $row->employee ? $row->employee->nik : '-';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? $row->employee->name : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'hadir' => 'success',
                    'terlambat' => 'warning',
                    'izin' => 'info',
                    'sakit' => 'primary',
                    'alpha' => 'danger',
                    'cuti' => 'secondary'
                ];
                $color = $badges[$row->status] ?? 'dark';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-employee_id="' . $row->employee_id . '" data-date="' . $row->date . '" data-time_in="' . $row->time_in . '" data-time_out="' . $row->time_out . '" data-status="' . $row->status . '"><i class="fas fa-edit"></i> Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(StoreAttendanceRequest $request)
    {
        if ($this->attendanceService->isLocked($request->date)) {
            return response()->json(['success' => false, 'message' => 'Periode sudah dikunci (Payroll).'], 403);
        }

        $attendance = $this->attendanceRepository->create($request->validated());
        $this->activityLogService->log('create_attendance', 'Menambahkan absensi manual untuk ID: ' . $attendance->employee_id);
        
        return response()->json(['success' => true, 'message' => 'Absensi berhasil ditambahkan.']);
    }

    public function update(UpdateAttendanceRequest $request, string $id)
    {
        if ($this->attendanceService->isLocked($request->date)) {
            return response()->json(['success' => false, 'message' => 'Periode sudah dikunci (Payroll).'], 403);
        }

        $this->attendanceRepository->update($id, $request->validated());
        $this->activityLogService->log('update_attendance', 'Memperbarui absensi ID: ' . $id);

        return response()->json(['success' => true, 'message' => 'Absensi berhasil diperbarui.']);
    }

    public function destroy(string $id)
    {
        $attendance = $this->attendanceRepository->findById((int)$id);
        if ($attendance && $this->attendanceService->isLocked($attendance->date)) {
            return response()->json(['success' => false, 'message' => 'Periode sudah dikunci (Payroll).'], 403);
        }

        $this->attendanceRepository->delete($id);
        $this->activityLogService->log('delete_attendance', 'Menghapus absensi ID: ' . $id);

        return response()->json(['success' => true, 'message' => 'Absensi berhasil dihapus.']);
    }

    public function downloadTemplate()
    {
        try {
            $filePath = $this->attendanceService->downloadTemplate();

            return response()->download($filePath, 'Template_Import_Absensi.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048'
        ]);

        try {
            $result = $this->attendanceService->importExcel($request->file('file'));

            $message = "Import selesai. Berhasil: {$result['success_rows']} baris, Gagal: {$result['failed_rows']} baris dari total {$result['total_rows']} data.";

            return response()->json([
                'success'      => true,
                'status'       => $result['status'],
                'message'      => $message,
                'total_rows'   => $result['total_rows'],
                'success_rows' => $result['success_rows'],
                'failed_rows'  => $result['failed_rows'],
                'errors'       => $result['errors'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                'errors'  => [],
            ], 500);
        }
    }
}
