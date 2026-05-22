<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
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
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:255', 'unique:positions,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'overtime_rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen yang dipilih tidak valid.',
            'code.required' => 'Kode posisi wajib diisi.',
            'code.string' => 'Kode posisi harus berupa teks.',
            'code.max' => 'Kode posisi tidak boleh lebih dari 255 karakter.',
            'code.unique' => 'Kode posisi sudah digunakan.',
            'name.required' => 'Nama posisi wajib diisi.',
            'name.string' => 'Nama posisi harus berupa teks.',
            'name.max' => 'Nama posisi tidak boleh lebih dari 255 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'overtime_rate.required' => 'Tarif lembur wajib diisi.',
            'overtime_rate.numeric' => 'Tarif lembur harus berupa angka.',
            'overtime_rate.min' => 'Tarif lembur tidak boleh kurang dari 0.',
        ];
    }
}
