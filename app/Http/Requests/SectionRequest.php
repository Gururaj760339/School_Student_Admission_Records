<?php

namespace App\Http\Requests;

use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Server-side validation for creating/updating a section.
 *
 * A section name must be unique within its parent class.
 */
class SectionRequest extends FormRequest
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
        $section = $this->route('section');
        $class = $this->route('class');

        if ($section instanceof Section) {
            $classId = $section->school_class_id;
            $ignoreId = $section->getKey();
        } else {
            $classId = $class instanceof SchoolClass ? $class->getKey() : null;
            $ignoreId = null;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sections', 'name')
                    ->where(fn ($query) => $query->where('school_class_id', $classId))
                    ->ignore($ignoreId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'section name'];
    }
}
