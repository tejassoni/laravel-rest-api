<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class ManageTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'category' => 'required|string|max:50|in:IT,Marketing,Finance,HR,Admin',
            'status' => 'required|integer|in:0,1',
            'days' => 'required|integer|min:1|max:365',
            'document' => 'required|file|mimes:jpeg,png,jpg,gif,xls,xlsx,csv,doc,docx,pdf,mp3,wav,mp4,mkv,avi,txt|max:5120', // Validation rules max file size 5mb
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The status field must be either 0 (Pending) or 1 (Completed).',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'error' => $validator->errors(),
            'data' => [],
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
