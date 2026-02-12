<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTutorialByTransitionTypeRequest extends FormRequest
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
            'transition_type_id' => ['required', 'integer', 'exists:transition_types,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'transition_type_id.required' => 'Transition Type ID is required.',
            'transition_type_id.integer' => 'Transition Type ID must be an integer.',
            'transition_type_id.exists' => 'Selected Transition Type does not exist.',
        ];
    }
}
