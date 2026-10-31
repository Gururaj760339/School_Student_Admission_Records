<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdmissionRequest;
use App\Http\Requests\StudentRequest;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Support\AdmissionFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public admission workflow for one school.
 *
 * The school is always resolved from the token in the URL. Browser input is
 * never trusted for the school id, so a visitor cannot submit a record into a
 * different school by changing a hidden field.
 */
class AdmissionController extends Controller
{
    public function create(Request $request, string $token): View
    {
        $school = $this->school($token);

        $record = $this->blankRecord();

        if ($pending = $this->pendingForSchool($school)) {
            $record = array_merge($record, $pending['data']);
        }

        return view('admission.form', $this->formData($school, $record));
    }

    public function preview(AdmissionRequest $request, string $token): View
    {
        $school = $this->school($token);

        $data = $this->normalize($request);
        $previewToken = (string) Str::uuid();

        $request->session()->put("admission.pending.$previewToken", [
            'school_id' => $school->id,
            'data' => $data,
            'created_at' => now()->timestamp,
        ]);
        $request->session()->put("admission.pending.school.{$school->id}", $previewToken);

        return view('admission.preview', [
            'school' => $school,
            'className' => $this->className($school, $data['class_id'] ?? null),
            'sectionName' => $this->sectionName($school, $data['class_id'] ?? null, $data['section_id'] ?? null),
            'fields' => AdmissionFields::all(),
            'record' => $data,
            'token' => $previewToken,
            'error' => null,
        ]);
    }

    public function edit(Request $request, string $token): View|RedirectResponse
    {
        $school = $this->school($token);
        $pending = $this->pendingForSchool($school);

        if ($pending === null) {
            return redirect()
                ->route('admission.create', ['token' => $school->admission_token])
                ->with('error', 'There is no preview to edit. Please complete the form first.');
        }

        return view('admission.form', $this->formData(
            $school,
            array_merge($this->blankRecord(), $pending['data']),
            'Review and edit your details, then preview again.'
        ));
    }

    public function submit(Request $request, string $token): View|RedirectResponse
    {
        $school = $this->school($token);
        $previewToken = (string) $request->input('submission_token', '');
        $pending = $previewToken !== ''
            ? $request->session()->get("admission.pending.$previewToken")
            : null;

        if (! is_array($pending) || (int) ($pending['school_id'] ?? 0) !== $school->id) {
            return redirect()
                ->route('admission.create', ['token' => $school->admission_token])
                ->with('error', 'This submission has already been processed or has expired. The form was reset to prevent duplicate records.');
        }

        $validator = Validator::make(
            $pending['data'],
            AdmissionFields::rules(),
            (new StudentRequest)->messages(),
            AdmissionFields::labels(),
        );

        if ($validator->fails()) {
            return redirect()
                ->route('admission.create', ['token' => $school->admission_token])
                ->withErrors($validator)
                ->withInput($pending['data'])
                ->with('error', 'Please correct the highlighted fields and preview again.');
        }

        $data = AdmissionFields::normalize($validator->validated());
        $classId = $pending['data']['class_id'] ?? null;
        $sectionId = $pending['data']['section_id'] ?? null;

        $class = $classId !== null
            ? $school->classes()->find($classId)
            : null;

        if ($classId !== null && $class === null) {
            return $this->invalidSelection($school, 'The selected class is no longer available for this school.');
        }

        $section = $sectionId !== null
            ? Section::query()
                ->where('school_class_id', $class?->id)
                ->find($sectionId)
            : null;

        if ($sectionId !== null && $section === null) {
            return $this->invalidSelection($school, 'The selected section is no longer available for this class.');
        }

        $attributes = $data + [
            'school_id' => $school->id,
            'class_id' => $class?->id,
            'section_id' => $section?->id,
            'submission_token' => $previewToken,
        ];

        // The unique submission_token makes double-clicks and browser retries
        // idempotent even when two requests arrive at the same time.
        $student = Student::firstOrCreate(
            ['submission_token' => $previewToken],
            $attributes
        );

        if (! $student->wasRecentlyCreated) {
            $request->session()->forget("admission.pending.$previewToken");
            $request->session()->forget("admission.pending.school.{$school->id}");

            return redirect()
                ->route('admission.create', ['token' => $school->admission_token])
                ->with('error', 'This admission was already submitted. The form was reset to prevent duplicate records.');
        }

        $request->session()->forget("admission.pending.$previewToken");
        $request->session()->forget("admission.pending.school.{$school->id}");

        return view('admission.success', [
            'school' => $school,
            'className' => $class?->name,
            'sectionName' => $section?->name,
            'record' => $data + ['record_id' => $student->id],
        ]);
    }

    private function school(string $token): School
    {
        return School::query()->where('admission_token', $token)->firstOrFail();
    }

    /**
     * @return array<string, string|null>
     */
    private function blankRecord(): array
    {
        return AdmissionFields::blank() + [
            'class_id' => null,
            'section_id' => null,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function normalize(AdmissionRequest $request): array
    {
        $data = AdmissionFields::normalize($request->validated());

        $data['class_id'] = $this->stringOrNull($request->validated('class_id'));
        $data['section_id'] = $this->stringOrNull($request->validated('section_id'));

        return $data;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function className(School $school, mixed $classId): ?string
    {
        if ($classId === null || $classId === '') {
            return null;
        }

        return $school->classes()->whereKey($classId)->value('name');
    }

    private function sectionName(School $school, mixed $classId, mixed $sectionId): ?string
    {
        if ($classId === null || $sectionId === null || $classId === '' || $sectionId === '') {
            return null;
        }

        $class = $school->classes()->whereKey($classId)->first();

        if ($class === null) {
            return null;
        }

        return $class->sections()->whereKey($sectionId)->value('name');
    }

    private function invalidSelection(School $school, string $message): RedirectResponse
    {
        return redirect()
            ->route('admission.create', ['token' => $school->admission_token])
            ->with('error', $message);
    }

    /**
     * @param  array<string, string|null>  $record
     * @return array<string, mixed>
     */
    private function formData(School $school, array $record, ?string $notice = null): array
    {
        return [
            'school' => $school,
            'classes' => $school->classes()
                ->with(['sections' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'fields' => AdmissionFields::all(),
            'required' => AdmissionFields::required(),
            'record' => $record,
            'notice' => $notice,
            'logoUrl' => $school->logoUrl() ?: asset('images/logo.png'),
        ];
    }

    /**
     * @return array{school_id: int, data: array<string, string|null>, created_at: int}|null
     */
    private function pendingForSchool(School $school): ?array
    {
        $previewToken = session()->get("admission.pending.school.{$school->id}");

        if (! is_string($previewToken) || $previewToken === '') {
            return null;
        }

        $pending = session()->get("admission.pending.$previewToken");

        if (! is_array($pending) || (int) ($pending['school_id'] ?? 0) !== $school->id) {
            return null;
        }

        return $pending;
    }
}
