<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:255', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode departemen wajib diisi.',
            'code.string' => 'Kode departemen harus berupa teks.',
            'code.max' => 'Kode departemen tidak boleh lebih dari 255 karakter.',
            'code.unique' => 'Kode departemen sudah digunakan.',
            'name.required' => 'Nama departemen wajib diisi.',
            'name.string' => 'Nama departemen harus berupa teks.',
            'name.max' => 'Nama departemen tidak boleh lebih dari 255 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
