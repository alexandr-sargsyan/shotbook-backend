<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTutorialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Проверка авторизации через middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'tutorial_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'label.required' => 'Label is required.',
            'label.string' => 'Label must be a string.',
            'label.max' => 'Label may not be greater than 255 characters.',
            'tutorial_url.url' => 'Tutorial URL must be a valid URL.',
            'tutorial_url.max' => 'Tutorial URL may not be greater than 2048 characters.',
        ];
    }
}
