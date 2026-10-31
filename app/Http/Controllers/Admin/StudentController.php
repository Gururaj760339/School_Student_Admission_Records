<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Support\AdmissionFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Authenticated management of student records for one school.
 *
 * Every query is filtered by school_id, so one school's records can never be
 * listed, viewed, edited or deleted through another school's URLs.
 */
class StudentController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request, School $school): View
    {
        $classId = $this->resolveClassFilter($request->query('class_id'), $school);
        $sectionId = $this->resolveSectionFilter($request->query('section_id'), $school, $classId);
        $search = trim((string) $request->query('search', ''));
        $gender = $this->resolveGenderFilter($request->query('gender'));

        $students = Student::query()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'section'])
            ->when($classId !== null, fn (Builder $query) => $query->where('class_id', $classId))
            ->when($sectionId !== null, fn (Builder $query) => $query->where('section_id', $sectionId))
            ->when($gender !== null, fn (Builder $query) => $query->where('gender', $gender))
            ->when($search !== '', function (Builder $query) use ($search) {
                foreach (preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) as $term) {
                    $needle = '%'.$term.'%';

                    $query->where(function (Builder $query) use ($needle) {
                        $query->where('first_name', 'like', $needle)
                            ->orWhere('last_name', 'like', $needle)
                            ->orWhere('admission_number', 'like', $needle)
                            ->orWhere('roll_no', 'like', $needle)
                            ->orWhere('mobile', 'like', $needle);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.students.index', [
            'school' => $school,
            'students' => $students,
            'classes' => $school->classes()
                ->with(['sections' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'classFilter' => $classId,
            'sectionFilter' => $sectionId,
            'search' => $search,
            'gender' => $gender,
            'serviceError' => null,
        ]);
    }

    public function show(School $school, Student $student): View
    {
        $this->assertBelongsToSchool($school, $student);

        $student->load(['schoolClass', 'section']);

        return view('admin.students.show', [
            'school' => $school,
            'student' => $student,
            'record' => $this->record($student),
            'className' => $student->schoolClass?->name,
            'sectionName' => $student->section?->name,
            'fields' => AdmissionFields::all(),
        ]);
    }

    public function edit(School $school, Student $student): View
    {
        $this->assertBelongsToSchool($school, $student);

        $student->load(['schoolClass', 'section']);

        return view('admin.students.edit', [
            'school' => $school,
            'student' => $student,
            'record' => $this->record($student),
            'className' => $student->schoolClass?->name,
            'sectionName' => $student->section?->name,
            'fields' => AdmissionFields::all(),
            'required' => AdmissionFields::required(),
        ]);
    }

    public function update(StudentRequest $request, School $school, Student $student): RedirectResponse
    {
        $this->assertBelongsToSchool($school, $student);

        $student->update($request->validated());

        return redirect()
            ->route('admin.students.index', $school)
            ->with('success', 'Student record updated successfully.');
    }

    public function destroy(School $school, Student $student): RedirectResponse
    {
        $this->assertBelongsToSchool($school, $student);

        $student->delete();

        return redirect()
            ->route('admin.students.index', $school)
            ->with('success', 'Student record deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function record(Student $student): array
    {
        $record = $student->only(AdmissionFields::names());
        $record[AdmissionFields::RECORD_ID] = $student->id;

        return $record;
    }

    private function resolveClassFilter(mixed $value, School $school): ?int
    {
        $value = is_string($value) ? trim($value) : '';

        if ($value === '' || $value === 'all') {
            return null;
        }

        $id = (int) $value;

        return $school->classes()->whereKey($id)->exists() ? $id : null;
    }

    private function resolveSectionFilter(mixed $value, School $school, ?int $classId): ?int
    {
        $value = is_string($value) ? trim($value) : '';

        if ($value === '' || $value === 'all') {
            return null;
        }

        $id = (int) $value;

        $exists = Section::query()
            ->whereKey($id)
            ->when($classId !== null, fn ($query) => $query->where('school_class_id', $classId))
            ->whereHas('schoolClass', fn ($query) => $query->where('school_id', $school->id))
            ->exists();

        return $exists ? $id : null;
    }

    private function resolveGenderFilter(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return in_array($value, ['Male', 'Female', 'Other'], true) ? $value : null;
    }

    private function assertBelongsToSchool(School $school, Student $student): void
    {
        abort_unless((int) $student->school_id === (int) $school->id, 404);
    }
}
