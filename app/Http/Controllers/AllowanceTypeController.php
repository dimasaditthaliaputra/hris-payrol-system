<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAllowanceTypeRequest;
use App\Http\Requests\UpdateAllowanceTypeRequest;
use App\Repositories\Contracts\AllowanceTypeRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AllowanceTypeController extends Controller
{
    public function __construct(
        protected AllowanceTypeRepositoryInterface $allowanceTypeRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function index(): View
    {
        return view('allowance_types.index');
    }

    public function data(): JsonResponse
    {
        $allowances = $this->allowanceTypeRepository->all();

        return DataTables::of($allowances)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-name="' . $row->name . '" data-description="' . $row->description . '"><i class="fas fa-edit"></i> Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StoreAllowanceTypeRequest $request): JsonResponse
    {
        try {
            $allowanceType = $this->allowanceTypeRepository->create($request->validated());
            $this->activityLogService->log('create_allowance_type', 'Menambahkan tipe tunjangan: ' . $allowanceType->name);
            
            return response()->json(['success' => true, 'message' => 'Tipe tunjangan berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan tipe tunjangan: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateAllowanceTypeRequest $request, string $id): JsonResponse
    {
        try {
            $this->allowanceTypeRepository->update($id, $request->validated());
            $this->activityLogService->log('update_allowance_type', 'Memperbarui tipe tunjangan ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Tipe tunjangan berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui tipe tunjangan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowanceTypeRepository->delete($id);
            $this->activityLogService->log('delete_allowance_type', 'Menghapus tipe tunjangan ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Tipe tunjangan berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus tipe tunjangan: ' . $e->getMessage()], 500);
        }
    }
}
