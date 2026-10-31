<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(): View
    {
        $schools = School::query()
            ->withCount(['classes', 'students'])
            ->orderBy('name')
            ->get();

        return view('admin.schools.index', [
            'schools' => $schools,
        ]);
    }

    public function store(SchoolRequest $request): RedirectResponse
    {
        $school = School::create([
            'name' => trim((string) $request->validated('name')),
        ]);

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('success', 'School "'.$school->name.'" created successfully.');
    }

    public function show(School $school): View
    {
        $school->loadCount(['classes', 'students']);

        return view('admin.schools.show', [
            'school' => $school,
        ]);
    }

    public function edit(School $school): View
    {
        return view('admin.schools.edit', [
            'school' => $school,
        ]);
    }

    public function update(SchoolRequest $request, School $school): RedirectResponse
    {
        $name = trim((string) $request->validated('name'));

        if ($name !== $school->name) {
            $school->update([
                'name' => $name,
                'slug' => School::generateSlug($name, $school->id),
            ]);
        }

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('success', 'School updated successfully.');
    }

    public function destroy(School $school): RedirectResponse
    {
        $school->loadCount(['classes', 'students']);

        if ($school->classes_count > 0 || $school->students_count > 0) {
            return redirect()
                ->route('admin.schools.index')
                ->with('error', 'Cannot delete "'.$school->name.'" while it still has classes or students. Remove those records first.');
        }

        if ($school->logo_path !== null) {
            Storage::disk('local')->delete($school->logo_path);
        }

        $name = $school->name;
        $school->delete();

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'School "'.$name.'" deleted.');
    }
}
