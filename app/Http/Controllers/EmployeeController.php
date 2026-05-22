<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportEmployeeRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Repositories\Contracts\AllowanceTypeRepositoryInterface;
use App\Repositories\Contracts\DeductionTypeRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
        protected EmployeeService $employeeService,
        protected DepartmentRepositoryInterface $departmentRepository,
        protected PositionRepositoryInterface $positionRepository,
        protected AllowanceTypeRepositoryInterface $allowanceTypeRepository,
        protected DeductionTypeRepositoryInterface $deductionTypeRepository
    ) {}

    public function index()
    {
        return view('employees.index');
    }

    public function data(): JsonResponse
    {
        $employees = $this->employeeRepository->all();

        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('department', function ($row) {
                return $row->department->name ?? '-';
            })
            ->addColumn('position', function ($row) {
                return $row->position->name ?? '-';
            })
            ->addColumn('action', function ($row) {
                $showUrl = route('employees.show', $row->id);
                $editUrl = route('employees.edit', $row->id);
                $btn  = '<a href="' . $showUrl . '" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i> Detail</a>';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i> Edit</a>';
                $btn .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(string $id)
    {
        // Load employee beserta relasi departemen, posisi, tunjangan (allowances) dan potongan (deductions)
        $employee = $this->employeeRepository->findOrFail((int)$id);
        
        // Memuat detail tipe tunjangan & potongan agar nama relasi terbaca lengkap
        $employee->load(['department', 'position', 'allowances.allowanceType', 'deductions.deductionType']);

        return view('employees.show', compact('employee'));
    }

    public function create()
    {
        $departments    = $this->departmentRepository->all();
        $positions      = $this->positionRepository->all();
        $allowanceTypes = $this->allowanceTypeRepository->all();
        $deductionTypes = $this->deductionTypeRepository->all();

        return view('employees.create', compact('departments', 'positions', 'allowanceTypes', 'deductionTypes'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $this->employeeService->createEmployee($request->validated());
            return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan karyawan: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $employee       = $this->employeeRepository->find($id);
        $departments    = $this->departmentRepository->all();
        $positions      = $this->positionRepository->all();
        $allowanceTypes = $this->allowanceTypeRepository->all();
        $deductionTypes = $this->deductionTypeRepository->all();

        return view('employees.edit', compact('employee', 'departments', 'positions', 'allowanceTypes', 'deductionTypes'));
    }

    public function update(UpdateEmployeeRequest $request, string $id)
    {
        try {
            $this->employeeService->updateEmployee($id, $request->validated());
            return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data karyawan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->employeeService->deleteEmployee($id);
            return response()->json(['success' => true, 'message' => 'Karyawan berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus karyawan: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    //  IMPORT EXCEL
    // =========================================================

    /**
     * Proses upload & import file Excel karyawan.
     */
    public function importExcel(ImportEmployeeRequest $request): JsonResponse
    {
        try {
            $result = $this->employeeService->importFromExcel($request->file('file'));

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

    /**
     * Download template Excel kosong untuk import karyawan.
     */
    public function downloadTemplate()
    {
        try {
            $filePath = $this->employeeService->downloadTemplate();

            return response()->download($filePath, 'Template_Import_Karyawan.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }
}
