<?php

namespace App\Http\Requests;

use App\Models\School;
use Illuminate\Validation\Rule;

class AdmissionRequest extends StudentRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $school = $this->school();

        $rules = parent::rules();

        $rules['class_id'] = [
            'nullable',
            'integer',
            $school !== null
                ? Rule::exists('school_classes', 'id')->where('school_id', $school->id)
                : 'exists:school_classes,id',
        ];

        $rules['section_id'] = [
            'nullable',
            'integer',
            $school !== null
                ? Rule::exists('sections', 'id')->where('school_class_id', $this->input('class_id'))
                : 'exists:sections,id',
        ];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return parent::attributes() + [
            'class_id' => 'class',
            'section_id' => 'section',
        ];
    }

    private function school(): ?School
    {
        $token = (string) $this->route('token');

        if ($token === '') {
            return null;
        }

        return School::query()->where('admission_token', $token)->first();
    }
}
