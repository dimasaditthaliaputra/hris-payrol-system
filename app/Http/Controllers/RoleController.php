<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    /**
     * Display a listing of the roles.
     */
    public function index(): View
    {
        return view('roles.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data(): JsonResponse
    {
        $roles = $this->roleRepository->all();

        return DataTables::of($roles)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '" data-name="' . $row->name . '" data-display_name="' . $row->display_name . '" data-description="' . $row->description . '">Edit</button>';
                $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '">Delete</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $this->roleRepository->create($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil ditambahkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $this->roleRepository->update($id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Prevent deleting core roles if necessary, e.g.
            $role = $this->roleRepository->find($id);
            if (in_array($role->name, ['super_admin', 'hrd', 'finance', 'karyawan'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role bawaan sistem tidak boleh dihapus.'
                ], 403);
            }

            $this->roleRepository->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus role: ' . $e->getMessage()
            ], 500);
        }
    }
}
