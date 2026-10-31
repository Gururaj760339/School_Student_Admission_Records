<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use App\Http\Requests\SectionRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Authenticated management of classes and sections inside one school.
 *
 * Classes and sections are stored only in the database. Google Sheets service
 * classes are intentionally left in the project for backwards compatibility,
 * but this controller no longer reads or writes worksheets.
 */
class ClassSectionController extends Controller
{
    public function index(School $school): View
    {
        $classes = $school->classes()
            ->with(['sections' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.classes.index', [
            'school' => $school,
            'classes' => $classes,
            'usesDatabase' => true,
        ]);
    }

    public function storeClass(ClassRequest $request, School $school): RedirectResponse
    {
        $sectionData = $request->validate([
            'section_name' => ['nullable', 'string', 'max:100'],
        ]);

        $name = trim((string) $request->validated('name'));
        $sectionName = trim((string) ($sectionData['section_name'] ?? ''));

        $class = $school->classes()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        $isNewClass = false;

        if (! $class) {
            $class = $school->classes()->create([
                'name' => $name,
                'slug' => SchoolClass::generateSlug($name, $school->id),
            ]);

            $isNewClass = true;
        }

        if ($sectionName === '') {
            return back()->with(
                'success',
                $isNewClass
                    ? 'Class "'.$class->name.'" created successfully.'
                    : 'Class "'.$class->name.'" already exists.'
            );
        }

        $sectionExists = $class->sections()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($sectionName)])
            ->exists();

        if ($sectionExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'section_name' => 'Section "'.$sectionName
                        .'" already exists in class "'.$class->name.'".',
                ]);
        }

        $section = $class->sections()->create([
            'name' => $sectionName,
            'slug' => Section::generateSlug($class, $sectionName),
        ]);

        return back()->with(
            'success',
            ($isNewClass
                ? 'Class "'.$class->name.'" and section "'.$section->name.'" created successfully.'
                : 'Section "'.$section->name.'" added to existing class "'.$class->name.'".')
        );
    }

    public function updateClass(ClassRequest $request, School $school, SchoolClass $class): RedirectResponse
    {
        $this->assertClassBelongsToSchool($school, $class);

        $class->update(['name' => trim((string) $request->validated('name'))]);

        return back()->with('success', 'Class renamed to "'.$class->name.'".');
    }

    public function destroyClass(School $school, SchoolClass $class): RedirectResponse
    {
        $this->assertClassBelongsToSchool($school, $class);

        $sectionIds = $class->sections()->pluck('id');

        $hasStudents = $class->students()->exists()
            || ($sectionIds->isNotEmpty()
                && Student::query()->whereIn('section_id', $sectionIds)->exists());

        if ($hasStudents) {
            return back()->with('error', 'Cannot delete class "'.$class->name.'" while students are assigned to it.');
        }

        $name = $class->name;
        $class->delete();

        return back()->with('success', 'Class "'.$name.'" deleted.');
    }

    public function storeSection(SectionRequest $request, School $school, SchoolClass $class): RedirectResponse
    {
        $this->assertClassBelongsToSchool($school, $class);

        $name = trim((string) $request->validated('name'));

        $exists = $class->sections()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'This section already exists in this class.']);
        }

        $section = $class->sections()->create([
            'name' => $name,
            'slug' => Section::generateSlug($class, $name),
        ]);

        return back()->with('success', 'Section "'.$section->name.'" created.');
    }

    public function updateSection(
        SectionRequest $request,
        School $school,
        SchoolClass $class,
        Section $section
    ): RedirectResponse {
        $this->assertSectionBelongsToClass($school, $class, $section);

        $section->update(['name' => trim((string) $request->validated('name'))]);

        return back()->with('success', 'Section renamed to "'.$section->name.'".');
    }

    public function destroySection(
        School $school,
        SchoolClass $class,
        Section $section
    ): RedirectResponse {
        $this->assertSectionBelongsToClass($school, $class, $section);

        if ($section->students()->exists()) {
            return back()->with('error', 'Cannot delete section "'.$section->name.'" while students are assigned to it.');
        }

        $name = $section->name;
        $section->delete();

        return back()->with('success', 'Section "'.$name.'" deleted.');
    }

    private function assertClassBelongsToSchool(School $school, SchoolClass $class): void
    {
        abort_unless((int) $class->school_id === (int) $school->id, 404);
    }

    private function assertSectionBelongsToClass(School $school, SchoolClass $class, Section $section): void
    {
        $this->assertClassBelongsToSchool($school, $class);

        abort_unless((int) $section->school_class_id === (int) $class->id, 404);
    }
}
