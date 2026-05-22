<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeductionTypeRequest;
use App\Http\Requests\UpdateDeductionTypeRequest;
use App\Repositories\Contracts\DeductionTypeRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DeductionTypeController extends Controller
{
    public function __construct(
        protected DeductionTypeRepositoryInterface $deductionTypeRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function index(): View
    {
        return view('deduction_types.index');
    }

    public function data(): JsonResponse
    {
        $deductions = $this->deductionTypeRepository->all();

        return DataTables::of($deductions)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-name="' . $row->name . '" data-description="' . $row->description . '"><i class="fas fa-edit"></i> Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(StoreDeductionTypeRequest $request): JsonResponse
    {
        try {
            $deductionType = $this->deductionTypeRepository->create($request->validated());
            $this->activityLogService->log('create_deduction_type', 'Menambahkan tipe potongan: ' . $deductionType->name);
            
            return response()->json(['success' => true, 'message' => 'Tipe potongan berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan tipe potongan: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateDeductionTypeRequest $request, string $id): JsonResponse
    {
        try {
            $this->deductionTypeRepository->update($id, $request->validated());
            $this->activityLogService->log('update_deduction_type', 'Memperbarui tipe potongan ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Tipe potongan berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui tipe potongan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->deductionTypeRepository->delete($id);
            $this->activityLogService->log('delete_deduction_type', 'Menghapus tipe potongan ID: ' . $id);

            return response()->json(['success' => true, 'message' => 'Tipe potongan berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus tipe potongan: ' . $e->getMessage()], 500);
        }
    }
}
