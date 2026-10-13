<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogoRequest;
use App\Support\SchoolBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticated upload/replacement of the school logo, plus the public
 * endpoint that serves the stored logo to the admission form.
 */
class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('branding.logo', [
            'logoUrl' => SchoolBranding::url(),
        ]);
    }

    public function store(LogoRequest $request): RedirectResponse
    {
        SchoolBranding::store($request->file('logo'));

        return redirect()
            ->route('branding.logo.edit')
            ->with('success', 'The school logo was uploaded successfully.');
    }

    public function image(): Response
    {
        $path = SchoolBranding::logoPath();

        abort_if($path === null, 404);

        return Storage::disk(SchoolBranding::DISK)->response($path);
    }
}
