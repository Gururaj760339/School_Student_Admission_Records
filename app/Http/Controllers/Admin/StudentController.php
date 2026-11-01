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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

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

    public function export(School $school): StreamedResponse
    {
        $students = Student::query()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'section'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $fields = AdmissionFields::names();
        $headers = array_merge(['id', 'School Name', 'Class Name', 'Section Name'], $fields);
        $filename = 'students-'.(Str::slug($school->name) ?: 'school').'.xlsx';

        return response()->streamDownload(function () use ($students, $fields, $headers, $school) {
            $path = tempnam(sys_get_temp_dir(), 'students-export-');
            $zip = new ZipArchive;
            $zip->open($path, ZipArchive::OVERWRITE);

            $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
            $zip->addFromString('_rels/.rels', $this->xlsxRootRelationships());
            $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook());
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelationships());
            $zip->addFromString('xl/styles.xml', $this->xlsxStyles());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxWorksheet($students, $fields, $headers, $school));

            $zip->close();

            readfile($path);
            @unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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

    private function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function xlsxRootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Students" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function xlsxWorkbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2">'
            .'<font><sz val="11"/><name val="Calibri"/><family val="2"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/><family val="2"/></font>'
            .'</fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  list<string>  $fields
     * @param  list<string>  $headers
     */
    private function xlsxWorksheet($students, array $fields, array $headers, School $school): string
    {
        $lastColumn = $this->columnLetter(count($headers) - 1);
        $lastRow = max(1, $students->count() + 1);

        $cols = '';
        foreach ($headers as $index => $header) {
            $width = in_array($header, ['current_address', 'previous_school_details', 'note'], true)
                ? 32
                : max(10, min(40, mb_strlen($header) + 4));
            $cols .= '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.$width.'" customWidth="1"/>';
        }

        $rows = '<row r="1">';
        foreach ($headers as $index => $header) {
            $rows .= '<c r="'.$this->columnLetter($index).'1" s="1" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($header).'</t></is></c>';
        }
        $rows .= '</row>';

        $rowNumber = 2;
        foreach ($students as $student) {
            $values = array_merge(
                [$student->id, $school->name, $student->schoolClass?->name, $student->section?->name],
                array_map(fn (string $field) => $student->{$field}, $fields)
            );

            $rows .= '<row r="'.$rowNumber.'">';
            foreach ($values as $index => $value) {
                $cell = $this->columnLetter($index).$rowNumber;

                if ($index === 0 && $value !== null && $value !== '') {
                    $rows .= '<c r="'.$cell.'"><v>'.(int) $value.'</v></c>';
                } elseif ($value === null || $value === '') {
                    $rows .= '<c r="'.$cell.'"/>';
                } else {
                    $rows .= '<c r="'.$cell.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $value).'</t></is></c>';
                }
            }
            $rows .= '</row>';
            $rowNumber++;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<dimension ref="A1:'.$lastColumn.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .'<cols>'.$cols.'</cols>'
            .'<sheetData>'.$rows.'</sheetData>'
            .'<autoFilter ref="A1:'.$lastColumn.$lastRow.'"/>'
            .'</worksheet>';
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        $index++;

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $index = intdiv($index - $mod, 26);
        }

        return $letter;
    }

    private function xml(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
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
