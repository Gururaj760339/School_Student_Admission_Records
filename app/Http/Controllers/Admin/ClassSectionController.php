<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GoogleSheetsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use App\Http\Requests\SectionRequest;
use App\Jobs\SyncSectionWorksheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Services\GoogleSheetsService;
use App\Services\WorksheetSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

/**
 * Authenticated management of classes and sections.
 *
 * Saving a class/section writes to the database first; the matching Google
 * Sheets worksheet is then synchronised immediately. When Google Sheets is
 * unavailable the database write is preserved, the failure is recorded on the
 * section, and a retryable queued job is dispatched.
 */
class ClassSectionController extends Controller
{
    public function __construct(
        private readonly WorksheetSynchronizer $synchronizer,
        private readonly GoogleSheetsService $sheets,
    ) {}

    public function index(): View
    {
        $classes = SchoolClass::with(['sections' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.classes.index', [
            'classes' => $classes,
            'usesDatabase' => $classes->isNotEmpty(),
        ]);
    }

    
    

public function storeClass(ClassRequest $request): RedirectResponse
{
    // Validate the section name before creating any records.
    $sectionData = $request->validate([
        'section_name' => [
            'required',
            'string',
            'max:100',
        ],
    ]);

    $name = trim((string) $request->validated('name'));
    $sectionName = trim($sectionData['section_name']);

    // Find an existing class using a case-insensitive name comparison.
    $class = SchoolClass::whereRaw(
        'LOWER(name) = ?',
        [mb_strtolower($name)]
    )->first();

    $isNewClass = false;

    // Create the class only if it does not already exist.
    if (!$class) {
        $class = SchoolClass::create([
            'name' => $name,
            'slug' => SchoolClass::generateSlug($name),
        ]);

        $isNewClass = true;
    }

    // Prevent duplicate section names within this class.
    $sectionExists = $class->sections()
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($sectionName)])
        ->exists();

    if ($sectionExists) {
        return back()
            ->withInput()
            ->withErrors([
                'section_name' => 'Section "' . $sectionName
                    . '" already exists in class "' . $class->name . '".',
            ]);
    }

    // Create the section under the selected or newly created class.
    $section = $class->sections()->create([
        'name' => $sectionName,
        'slug' => Section::generateSlug($class, $sectionName),
        'google_sync_status' => Section::SYNC_PENDING,
    ]);

    // Synchronize the Google Sheets worksheet.
    if ($error = $this->syncSection($section)) {
        return back()->with(
            'warning',
            ($isNewClass
                ? 'Class "' . $class->name . '" and section "' . $section->name . '" were saved'
                : 'Section "' . $section->name . '" was added to class "' . $class->name . '"')
            . ', but the Google worksheet could not be created yet. '
            . 'Use Retry sync to try again. Detail: ' . $error
        );
    }

    return back()->with(
        'success',
        ($isNewClass
            ? 'Class "' . $class->name . '" and section "' . $section->name . '" created successfully.'
            : 'Section "' . $section->name . '" added to existing class "' . $class->name . '".')
        . ' Worksheet "' . $section->worksheet . '" is ready.'
    );
}



    public function updateClass(ClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $oldName = $class->name;
        $class->update(['name' => (string) $request->validated('name')]);

        if ($oldName === $class->name) {
            return back()->with('success', 'Class name unchanged.');
        }

        // Section worksheets are named after the class, so they must be renamed.
        $errors = $this->syncSections($class->sections()->orderBy('name')->get());

        if ($errors !== []) {
            return back()->with(
                'warning',
                'Class renamed to "'.$class->name.'", but some worksheets could not be renamed yet. '
                .'They will be retried automatically. '.implode(' ', $errors)
            );
        }

        return back()->with('success', 'Class renamed to "'.$class->name.'" and worksheets synchronised.');
    }

    public function destroyClass(SchoolClass $class): RedirectResponse
    {
        $class->load('sections');

        foreach ($class->sections as $section) {
            if ($blocker = $this->deletionBlocker($section)) {
                return back()->with('error', 'Cannot delete this class: '.$blocker);
            }
        }

        foreach ($class->sections as $section) {
            $this->deleteSectionWorksheet($section);
        }

        $class->delete();

        return back()->with('success', 'Class deleted.');
    }

   
public function storeSection(
    SectionRequest $request,
    SchoolClass $class
): RedirectResponse {
    $name = trim((string) $request->validated('name'));

    // Prevent duplicate section names within the same class only.
    $exists = $class->sections()
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
        ->exists();

    if ($exists) {
        return back()
            ->withInput()
            ->withErrors([
                'name' => 'This section already exists in this class.',
            ]);
    }

    $section = $class->sections()->create([
        'name' => $name,
        'slug' => Section::generateSlug($class, $name),
        'google_sync_status' => Section::SYNC_PENDING,
    ]);

    if ($error = $this->syncSection($section)) {
        return back()->with(
            'warning',
            'Section "' . $section->name
            . '" was saved, but its Google worksheet could not be created. '
            . 'Use Retry sync. Detail: ' . $error
        );
    }

    return back()->with(
        'success',
        'Section "' . $section->name
        . '" created and worksheet "' . $section->worksheet
        . '" is ready.'
    );
}


    public function updateSection(SectionRequest $request, Section $section): RedirectResponse
    {
        $oldName = $section->name;
        $section->update(['name' => (string) $request->validated('name')]);

        if ($oldName === $section->name) {
            return back()->with('success', 'Section name unchanged.');
        }

        if ($error = $this->syncSection($section)) {
            return back()->with(
                'warning',
                'Section updated, but its worksheet rename is pending. It will be retried automatically, '
                .'or use Retry. Detail: '.$error
            );
        }

        return back()->with('success', 'Section renamed and worksheet "'.$section->worksheet.'" synchronised.');
    }

    public function destroySection(Section $section): RedirectResponse
    {
        if ($blocker = $this->deletionBlocker($section)) {
            return back()->with('error', 'Cannot delete this section: '.$blocker);
        }

        $name = $section->name;
        $this->deleteSectionWorksheet($section);
        $section->delete();

        return back()->with('success', 'Section "'.$name.'" deleted.');
    }

    public function sync(Section $section): RedirectResponse
    {
        if ($error = $this->syncSection($section)) {
            return back()->with('error', 'Synchronisation failed: '.$error);
        }

        return back()->with('success', 'Worksheet "'.$section->worksheet.'" is synchronised.');
    }

    /**
     * Synchronise one section, queuing a retry when Google Sheets is down.
     *
     * @return string|null an error message when the sync did not complete.
     */
    private function syncSection(Section $section): ?string
    {
        try {
            $this->synchronizer->sync($section);

            return null;
        } catch (GoogleSheetsException $e) {
            $this->queueRetry($section);

            return $e->getMessage();
        }
    }

    /**
     * @param  Collection<int, Section>  $sections
     * @return list<string>
     */
    private function syncSections(Collection $sections): array
    {
        $errors = [];

        foreach ($sections as $section) {
            if ($error = $this->syncSection($section)) {
                $errors[] = 'Section "'.$section->name.'": '.$error;
            }
        }

        return $errors;
    }

    private function queueRetry(Section $section): void
    {
        try {
            SyncSectionWorksheet::dispatch($section->id);
        } catch (Throwable) {
            // The failure is already recorded on the section; the admin panel
            // exposes a manual Retry action as a fallback.
        }
    }

    /**
     * Returns a human-readable reason when a section cannot be deleted, or null
     * when deletion is safe. Worksheets that still hold student records are
     * never deleted and block the operation to protect the data.
     */
    private function deletionBlocker(Section $section): ?string
    {
        $worksheet = $section->worksheet;

        if (! is_string($worksheet) || $worksheet === '') {
            return null;
        }

        try {
            if (! $this->sheets->worksheetExists($worksheet)) {
                return null;
            }

            if ($this->sheets->worksheetHasRecords($worksheet)) {
                return 'worksheet "'.$worksheet.'" still contains student records. '
                    .'Move or remove those records first, then delete the section.';
            }
        } catch (GoogleSheetsException $e) {
            return 'Google Sheets could not be checked ('.$e->getMessage().'). Nothing was deleted; retry once Sheets is available.';
        } catch (Throwable) {
            return 'the worksheet could not be checked. Nothing was deleted; retry once Google Sheets is available.';
        }

        return null;
    }

    private function deleteSectionWorksheet(Section $section): void
    {
        $worksheet = $section->worksheet;

        if (! is_string($worksheet) || $worksheet === '') {
            return;
        }

        try {
            $this->sheets->deleteWorksheet($worksheet);
        } catch (Throwable) {
            // Best effort: the worksheet is left untouched rather than risk data
            // loss. The section row is still removed from the database.
        }
    }
}
