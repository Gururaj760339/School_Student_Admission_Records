<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GoogleSheetsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Services\GoogleSheetsService;
use App\Support\AdmissionClasses;
use App\Support\AdmissionFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Authenticated admin management of student records.
 *
 * Records are read directly from Google Sheets; there is no database cache.
 * Every view/edit/delete operation is scoped to the class worksheet and the
 * record is located by its immutable record_id.
 */
class StudentController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private readonly GoogleSheetsService $sheets) {}

    public function index(Request $request): View
    {
        $classSlug = $this->resolveClassFilter($request->query('class'));
        $search = trim((string) $request->query('search', ''));
        $gender = $this->resolveGenderFilter($request->query('gender'));

        $slugs = $classSlug !== null ? [$classSlug] : AdmissionClasses::slugs();

        $records = collect();
        $serviceError = null;

        try {
            $records = collect($this->sheets->getRecordsForClasses($slugs));
        } catch (GoogleSheetsException $e) {
            $serviceError = $e->getMessage();
        }

        if ($search !== '') {
            $records = $this->applySearch($records, $search);
        }

        if ($gender !== null) {
            $records = $records->filter(fn ($record) => ($record['gender'] ?? null) === $gender)->values();
        }

        $page = max(1, (int) $request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $records->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $records->count(),
            self::PER_PAGE,
            $page,
            [
                'path' => route('admin.students.index'),
                'query' => $request->query(),
            ]
        );

        return view('admin.students.index', [
            'students' => $paginator,
            'classes' => AdmissionClasses::options(),
            'classFilter' => $classSlug,
            'search' => $search,
            'gender' => $gender,
            'serviceError' => $serviceError,
        ]);
    }

    public function show(string $class, string $recordId): View
    {
        $this->assertClass($class);

        try {
            $record = $this->sheets->findRecord(AdmissionClasses::worksheetFor($class), $recordId);
        } catch (GoogleSheetsException $e) {
            return view('admin.students.error', [
                'message' => $e->getMessage(),
            ]);
        }

        if ($record === null) {
            abort(404, 'Student record not found.');
        }

        return view('admin.students.show', [
            'record' => $record,
            'classSlug' => $class,
            'className' => AdmissionClasses::nameFor($class),
            'fields' => AdmissionFields::all(),
        ]);
    }

    public function edit(string $class, string $recordId): View
    {
        $this->assertClass($class);

        try {
            $record = $this->sheets->findRecord(AdmissionClasses::worksheetFor($class), $recordId);
        } catch (GoogleSheetsException $e) {
            return view('admin.students.error', [
                'message' => $e->getMessage(),
            ]);
        }

        if ($record === null) {
            abort(404, 'Student record not found.');
        }

        return view('admin.students.edit', [
            'record' => $record,
            'classSlug' => $class,
            'className' => AdmissionClasses::nameFor($class),
            'fields' => AdmissionFields::all(),
            'required' => AdmissionFields::required(),
        ]);
    }

    public function update(StudentRequest $request, string $class, string $recordId): RedirectResponse
    {
        $this->assertClass($class);

        try {
            $this->sheets->updateRecord(
                AdmissionClasses::worksheetFor($class),
                $recordId,
                $request->validated()
            );
        } catch (GoogleSheetsException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.students.index', ['class' => $class])
            ->with('success', 'Student record updated successfully.');
    }

    public function destroy(string $class, string $recordId): RedirectResponse
    {
        $this->assertClass($class);

        try {
            $this->sheets->deleteRecord(AdmissionClasses::worksheetFor($class), $recordId);
        } catch (GoogleSheetsException $e) {
            return redirect()
                ->route('admin.students.index', ['class' => $class])
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.students.index', ['class' => $class])
            ->with('success', 'Student record deleted successfully.');
    }

    /**
     * @param  Collection<int, array<string, string|null>>  $records
     * @return Collection<int, array<string, string|null>>
     */
    private function applySearch(Collection $records, string $search): Collection
    {
        $needle = Str::lower($search);

        return $records->filter(function ($record) use ($needle) {
            $candidates = [
                $record['admission_number'] ?? '',
                $record['first_name'] ?? '',
                $record['last_name'] ?? '',
                trim(($record['first_name'] ?? '').' '.($record['last_name'] ?? '')),
                $record['mobile'] ?? '',
                $record['roll_no'] ?? '',
            ];

            foreach ($candidates as $candidate) {
                if ($candidate !== '' && Str::contains(Str::lower((string) $candidate), $needle)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    private function resolveClassFilter(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        if ($value === '' || $value === 'all') {
            return null;
        }

        return AdmissionClasses::exists($value) ? $value : null;
    }

    private function resolveGenderFilter(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return in_array($value, ['Male', 'Female', 'Other'], true) ? $value : null;
    }

    private function assertClass(string $class): void
    {
        if (! AdmissionClasses::exists($class)) {
            abort(404, 'Unknown class.');
        }
    }
}
