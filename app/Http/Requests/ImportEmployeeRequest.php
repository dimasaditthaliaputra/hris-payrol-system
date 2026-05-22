<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isHrd());
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'  => 'File Excel wajib diunggah.',
            'file.file'      => 'Upload harus berupa file.',
            'file.mimes'     => 'Format file harus .xlsx atau .xls.',
            'file.max'       => 'Ukuran file tidak boleh lebih dari 10MB.',
        ];
    }
}
