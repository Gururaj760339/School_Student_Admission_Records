<?php

namespace App\Http\Requests;

use App\Support\AdmissionFields;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the 30 admission fields. The rule set is derived from
 * config/admission.php so validation, forms and sheet columns stay in sync.
 */
class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|string[]>>
     */
    public function rules(): array
    {
        return AdmissionFields::rules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'Please select a valid gender.',
            'date_of_birth.required' => 'The date of birth field is required.',
            'date_of_birth.date' => 'Please enter a valid date of birth.',
            'date_of_birth.before_or_equal' => 'The date of birth cannot be in the future.',
            'mobile.required' => 'The mobile field is required.',
            'mobile.regex' => 'Please enter a valid mobile number.',
            '*.email' => 'Please enter a valid email address.',
            '*.regex' => 'Please enter a valid phone number.',
            '*.numeric' => 'Please enter a numeric value.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return AdmissionFields::labels();
    }
}
