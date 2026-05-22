<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentRepositoryInterface $departmentRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function index(): View
    {
        return view('departments.index');
    }

    public function data(): JsonResponse
    {
        $departments = $this->departmentRepository->all();

        return DataTables::of($departments)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-code="' . $row->code . '" data-name="' . $row->name . '" data-description="' . $row->description . '"><i class="fas fa-edit"></i> Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        try {
            $department = $this->departmentRepository->create($request->validated());
            $this->activityLogService->log('create_department', 'Menambahkan departemen: ' . $department->name);
            
            return response()->json(['success' => true, 'message' => 'Departemen berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan departemen: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateDepartmentRequest $request, string $id): JsonResponse
    {
        try {
            $this->departmentRepository->update($id, $request->validated());
            $this->activityLogService->log('update_department', 'Memperbarui departemen ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Departemen berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui departemen: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            // Cek jika department dipakai di tabel positions. 
            // Meskipun kita pakai cascade/softDeletes, baiknya dicek kalau ada relasi.
            $department = $this->departmentRepository->find($id);
            if ($department->positions()->count() > 0) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat dihapus karena masih digunakan di Posisi.'], 403);
            }

            $this->departmentRepository->delete($id);
            $this->activityLogService->log('delete_department', 'Menghapus departemen ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Departemen berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus departemen: ' . $e->getMessage()], 500);
        }
    }
}
