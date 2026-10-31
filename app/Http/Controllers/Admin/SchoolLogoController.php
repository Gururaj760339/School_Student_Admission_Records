<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SchoolLogoController extends Controller
{
    public function edit(School $school): View
    {
        return view('branding.logo', [
            'school' => $school,
            'logoUrl' => $school->logoUrl() ?: asset('images/logo.png'),
        ]);
    }

    public function store(LogoRequest $request, School $school): RedirectResponse
    {
        $path = $request->file('logo')->store('school-logos', 'local');

        if ($school->logo_path !== null) {
            Storage::disk('local')->delete($school->logo_path);
        }

        $school->update(['logo_path' => $path]);

        return redirect()
            ->route('admin.logo.edit', $school)
            ->with('success', 'The logo for "'.$school->name.'" was uploaded successfully.');
    }

    public function image(string $token): Response
    {
        $school = School::query()
            ->where('admission_token', $token)
            ->firstOrFail();

        abort_if($school->logo_path === null, 404);

        return Storage::disk('local')->response($school->logo_path);
    }
}
