<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PositionController extends Controller
{
    public function __construct(
        protected PositionRepositoryInterface $positionRepository,
        protected DepartmentRepositoryInterface $departmentRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function index(): View
    {
        $departments = $this->departmentRepository->all();
        return view('positions.index', compact('departments'));
    }

    public function data(): JsonResponse
    {
        // Gunakan eager loading untuk department
        $positions = $this->positionRepository->all();

        return DataTables::of($positions)
            ->addIndexColumn()
            ->addColumn('department_name', function ($row) {
                return $row->department ? $row->department->name : '-';
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-department_id="' . $row->department_id . '" data-code="' . $row->code . '" data-name="' . $row->name . '" data-description="' . $row->description . '" data-overtime_rate="' . $row->overtime_rate . '"><i class="fas fa-edit"></i> Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        try {
            $position = $this->positionRepository->create($request->validated());
            $this->activityLogService->log('create_position', 'Menambahkan posisi: ' . $position->name);
            
            return response()->json(['success' => true, 'message' => 'Posisi berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan posisi: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdatePositionRequest $request, string $id): JsonResponse
    {
        try {
            $this->positionRepository->update($id, $request->validated());
            $this->activityLogService->log('update_position', 'Memperbarui posisi ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Posisi berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui posisi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->positionRepository->delete($id);
            $this->activityLogService->log('delete_position', 'Menghapus posisi ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Posisi berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus posisi: ' . $e->getMessage()], 500);
        }
    }
}
