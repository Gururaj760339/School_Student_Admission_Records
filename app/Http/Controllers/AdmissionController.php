<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleSheetsException;
use App\Http\Requests\StudentRequest;
use App\Services\GoogleSheetsService;
use App\Support\AdmissionClasses;
use App\Support\AdmissionFields;
use App\Support\SchoolBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public admission workflow: select a class, complete the form, preview,
 * and only then confirm & submit to Google Sheets.
 */
class AdmissionController extends Controller
{
    public function __construct(private readonly GoogleSheetsService $sheets) {}

    public function select(): View
    {
        return view('admission.select', [
            'classes' => AdmissionClasses::grouped(),
            'default' => AdmissionClasses::defaultSlug(),
        ]);
    }

    public function create(string $class): View
    {
        $this->assertClass($class);

        $record = AdmissionFields::blank();

        if ($pending = $this->pendingForClass($class)) {
            $record = array_merge($record, $pending['data']);
        }

        return view('admission.form', $this->formData($class, $record));
    }

    public function preview(StudentRequest $request, string $class): View
    {
        $this->assertClass($class);

        $data = AdmissionFields::normalize($request->validated());
        $token = (string) Str::uuid();

        $request->session()->put("admission.pending.$token", [
            'class' => $class,
            'data' => $data,
            'created_at' => now()->timestamp,
        ]);
        $request->session()->put("admission.pending_class.$class", $token);

        return view('admission.preview', [
            'classSlug' => $class,
            'className' => AdmissionClasses::nameFor($class),
            'fields' => AdmissionFields::all(),
            'record' => $data,
            'token' => $token,
            'error' => null,
        ]);
    }

    public function edit(Request $request, string $class): View|RedirectResponse
    {
        $this->assertClass($class);

        $pending = $this->pendingForClass($class);

        if ($pending === null) {
            return redirect()->route('admission.create', ['class' => $class]);
        }

        return view('admission.form', $this->formData($class, $pending['data'], 'Review and edit your details, then preview again.'));
    }

    public function submit(Request $request, string $class): View|RedirectResponse
    {
        $this->assertClass($class);

        $token = (string) $request->input('submission_token', '');
        $pending = $token !== '' ? $request->session()->get("admission.pending.$token") : null;

        if (! is_array($pending) || ($pending['class'] ?? null) !== $class) {
            return redirect()->route('admission.create', ['class' => $class])
                ->with('error', 'This submission has already been processed or has expired. The form was reset to prevent duplicate records.');
        }

        $validator = Validator::make(
            $pending['data'],
            AdmissionFields::rules(),
            (new StudentRequest)->messages(),
            AdmissionFields::labels(),
        );

        if ($validator->fails()) {
            return redirect()->route('admission.create', ['class' => $class])
                ->withErrors($validator)
                ->withInput($pending['data'])
                ->with('error', 'Please correct the highlighted fields and preview again.');
        }

        // Guard against concurrent double submissions (double-click / retry).
        $lockKey = "admission.submitting.$token";

        if (! Cache::add($lockKey, true, 300)) {
            return view('admission.preview', [
                'classSlug' => $class,
                'className' => AdmissionClasses::nameFor($class),
                'fields' => AdmissionFields::all(),
                'record' => $pending['data'],
                'token' => $token,
                'error' => 'This submission is already being processed. Please wait a moment before trying again.',
            ]);
        }

        try {
            $record = $this->sheets->appendRecord(
                AdmissionClasses::worksheetFor($class),
                $validator->validated()
            );
        } catch (GoogleSheetsException $e) {
            // Keep the pending payload so the user can retry; release the lock.
            Cache::forget($lockKey);

            return view('admission.preview', [
                'classSlug' => $class,
                'className' => AdmissionClasses::nameFor($class),
                'fields' => AdmissionFields::all(),
                'record' => $pending['data'],
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }

        // Success: consume the pending payload so the same token cannot be
        // submitted twice.
        $request->session()->forget("admission.pending.$token");
        $request->session()->forget("admission.pending_class.$class");
        Cache::forget($lockKey);

        return view('admission.success', [
            'classSlug' => $class,
            'className' => AdmissionClasses::nameFor($class),
            'record' => $record,
        ]);
    }

    /**
     * @param  array<string, string|null>  $record
     * @return array<string, mixed>
     */
    private function formData(string $class, array $record, ?string $notice = null): array
    {
        return [
            'classSlug' => $class,
            'className' => AdmissionClasses::nameFor($class),
            'fields' => AdmissionFields::all(),
            'required' => AdmissionFields::required(),
            'record' => $record,
            'notice' => $notice,
            'logoUrl' => SchoolBranding::url(),
        ];
    }

    private function assertClass(string $class): void
    {
        if (! AdmissionClasses::exists($class)) {
            abort(404, 'Unknown class.');
        }
    }

    /**
     * @return array{class: string, data: array<string, string|null>}|null
     */
    private function pendingForClass(string $class): ?array
    {
        $token = session()->get("admission.pending_class.$class");

        if (! is_string($token) || $token === '') {
            return null;
        }

        $pending = session()->get("admission.pending.$token");

        if (! is_array($pending) || ($pending['class'] ?? null) !== $class) {
            return null;
        }

        return $pending;
    }
}
