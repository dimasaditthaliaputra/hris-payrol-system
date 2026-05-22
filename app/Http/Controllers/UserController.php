<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected RoleRepositoryInterface $roleRepository,
        protected UserService $userService
    ) {}

    public function index()
    {
        $roles = $this->roleRepository->all();
        return view('users.index', compact('roles'));
    }

    public function data(): JsonResponse
    {
        $users = \App\Models\User::with('role')->get();

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('role_name', function ($row) {
                return $row->role ? $row->role->name : '-';
            })
            ->addColumn('status', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge bg-success">Aktif</span>';
                }
                return '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning edit-btn me-1" 
                    data-id="'.$row->id.'" 
                    data-name="'.$row->name.'" 
                    data-email="'.$row->email.'" 
                    data-role_id="'.$row->role_id.'" 
                    data-phone="'.$row->phone.'" 
                    data-is_active="'.$row->is_active.'"><i class="fas fa-edit"></i> Edit</button>';
                
                // Mencegah hapus diri sendiri
                if (auth()->id() != $row->id) {
                    $btn .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="'.$row->id.'"><i class="fas fa-trash"></i> Hapus</button>';
                }
                
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $this->userService->createUser($request->validated());
            return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan user: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $this->userService->updateUser($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'User berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui user: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus user: ' . $e->getMessage()], 500);
        }
    }
}
