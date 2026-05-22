<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Contracts\AttendanceRepositoryInterface;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isHrd());
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpha,cuti'],
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $attendanceRepository = app(AttendanceRepositoryInterface::class);
            if ($attendanceRepository->existsForEmployeeAndDate($this->employee_id, $this->date)) {
                $validator->errors()->add('date', 'Absensi untuk karyawan pada tanggal ini sudah ada.');
            }
        });
    }
}
