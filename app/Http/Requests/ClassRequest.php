<?php

namespace App\Http\Requests;

use App\Models\School;
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
        $school = $this->route('school');
        $ignoreId = $class instanceof SchoolClass ? $class->getKey() : null;
        $schoolId = $school instanceof School ? $school->getKey() : null;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('school_classes', 'name')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($ignoreId),
            ],
        ];

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
