<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeductionTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:deduction_types,name'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama potongan wajib diisi.',
            'name.string' => 'Nama potongan harus berupa teks.',
            'name.max' => 'Nama potongan tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Nama potongan sudah digunakan.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
