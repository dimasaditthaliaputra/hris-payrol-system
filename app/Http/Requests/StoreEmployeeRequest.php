<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isHrd());
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'string', 'max:50', 'unique:employees,nik'],
            'name' => ['required', 'string', 'max:255'],
            'place_of_birth' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['required', 'in:L,P'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'status_kerja' => ['required', 'in:Tetap,Kontrak,Freelance,Nonaktif'],
            'status_pajak' => ['required', 'in:TK/0,K/0,K/1,K/2,K/3'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:50'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:50'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'join_date' => ['required', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'nama_bank' => ['nullable', 'string', 'max:100'],
            'rekening_bank' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'allowances' => ['nullable', 'array'],
            'allowances.*.allowance_type_id' => ['required_with:allowances', 'exists:allowance_types,id'],
            'allowances.*.amount' => ['required_with:allowances', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'array'],
            'deductions.*.deduction_type_id' => ['required_with:deductions', 'exists:deduction_types,id'],
            'deductions.*.amount' => ['required_with:deductions', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.string' => 'NIK harus berupa teks.',
            'nik.max' => 'NIK tidak boleh lebih dari 50 karakter.',
            'nik.unique' => 'NIK sudah terdaftar di sistem.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'place_of_birth.max' => 'Tempat lahir tidak boleh lebih dari 100 karakter.',
            'date_of_birth.date' => 'Format tanggal lahir tidak valid.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin yang dipilih tidak valid (harus L atau P).',
            'phone.max' => 'Nomor HP tidak boleh lebih dari 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 100 karakter.',
            'status_kerja.required' => 'Status kerja wajib dipilih.',
            'status_kerja.in' => 'Status kerja tidak valid.',
            'status_pajak.required' => 'Status pajak wajib dipilih.',
            'status_pajak.in' => 'Status pajak tidak valid.',
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen yang dipilih tidak terdaftar.',
            'position_id.required' => 'Jabatan wajib dipilih.',
            'position_id.exists' => 'Jabatan yang dipilih tidak terdaftar.',
            'join_date.required' => 'Tanggal masuk wajib diisi.',
            'join_date.date' => 'Format tanggal masuk tidak valid.',
            'basic_salary.required' => 'Gaji pokok wajib diisi.',
            'basic_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'basic_salary.min' => 'Gaji pokok tidak boleh kurang dari 0.',
            'nama_bank.max' => 'Nama bank tidak boleh lebih dari 100 karakter.',
            'rekening_bank.max' => 'Nomor rekening bank tidak boleh lebih dari 50 karakter.',
            'profile_photo.image' => 'Foto profil harus berupa file gambar.',
            'profile_photo.mimes' => 'Format foto profil harus jpeg, png, atau jpg.',
            'profile_photo.max' => 'Ukuran foto profil tidak boleh lebih dari 2MB (2048 KB).',
            'allowances.*.allowance_type_id.required_with' => 'Tipe tunjangan wajib dipilih.',
            'allowances.*.allowance_type_id.exists' => 'Tipe tunjangan tidak valid.',
            'allowances.*.amount.required_with' => 'Nominal tunjangan wajib diisi.',
            'allowances.*.amount.numeric' => 'Nominal tunjangan harus berupa angka.',
            'allowances.*.amount.min' => 'Nominal tunjangan tidak boleh kurang dari 0.',
            'deductions.*.deduction_type_id.required_with' => 'Tipe potongan wajib dipilih.',
            'deductions.*.deduction_type_id.exists' => 'Tipe potongan tidak valid.',
            'deductions.*.amount.required_with' => 'Nominal potongan wajib diisi.',
            'deductions.*.amount.numeric' => 'Nominal potongan harus berupa angka.',
            'deductions.*.amount.min' => 'Nominal potongan tidak boleh kurang dari 0.',
        ];
    }
}
