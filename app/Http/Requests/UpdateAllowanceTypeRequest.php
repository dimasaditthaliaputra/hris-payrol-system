<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAllowanceTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('allowance_types')->ignore($this->allowance_type)],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tunjangan wajib diisi.',
            'name.string' => 'Nama tunjangan harus berupa teks.',
            'name.max' => 'Nama tunjangan tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Nama tunjangan sudah digunakan.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
