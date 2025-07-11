<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set this to `true` if you want to allow all users to upload files.
        // Alternatively, you can add authorization logic here (e.g., check user roles).
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'files' => 'required|array',
            'files.*' => [
                'required',
                'file',
                'mimes:csv,txt', // Only CSV and TXT files allowed
                'max:51200', // Max file size in KB (50MB)
            ],
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'files.required' => 'No files were uploaded.',
            'files.array' => 'The files must be provided as an array.',
            'files.*.required' => 'One or more files are missing.',
            'files.*.file' => 'The uploaded item must be a valid file.',
            'files.*.mimes' => 'Only CSV and TXT files are allowed.',
            'files.*.max' => 'Each file must not exceed 50MB in size.',
        ];
    }
}
