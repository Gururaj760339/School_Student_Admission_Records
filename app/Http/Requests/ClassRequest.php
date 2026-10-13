<?php

namespace App\Http\Requests;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Server-side validation for creating/updating a class.
 */
class ClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $class = $this->route('class');
        $ignoreId = $class instanceof SchoolClass ? $class->getKey() : null;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:100',
            ],
        ];

        if ($ignoreId !== null) {
            $rules['name'][] = Rule::unique('school_classes', 'name')->ignore($ignoreId);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'class name'];
    }
}
