<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($this->role)],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Kode role wajib diisi.',
            'name.string' => 'Kode role harus berupa teks.',
            'name.max' => 'Kode role tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Kode role sudah digunakan.',
            'display_name.required' => 'Nama tampilan wajib diisi.',
            'display_name.string' => 'Nama tampilan harus berupa teks.',
            'display_name.max' => 'Nama tampilan tidak boleh lebih dari 255 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
