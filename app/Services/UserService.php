<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ActivityLogService $activityLogService
    ) {}

    public function createUser(array $data)
    {
        DB::beginTransaction();
        try {
            $data['password'] = Hash::make($data['password']);
            $user = $this->userRepository->create($data);
            
            $this->activityLogService->log('create_user', 'Menambahkan user baru: ' . $user->email);
            
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->findOrFail((int) $id);

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $this->userRepository->update((int) $id, $data);
            
            $this->activityLogService->log('update_user', 'Memperbarui data user: ' . $user->email);
            
            DB::commit();
            return $this->userRepository->find((int) $id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUser(string $id)
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->findOrFail((int) $id);
            $this->userRepository->delete((int) $id);
            
            $this->activityLogService->log('delete_user', 'Menonaktifkan user: ' . $user->email);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
