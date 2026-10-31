<?php

use App\Http\Controllers\Admin\ClassSectionController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SchoolLogoController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BrandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('admission.select');
});

/*
|--------------------------------------------------------------------------
| Authentication (admin only; there is no public registration)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Installation-wide logo (kept for backwards compatibility)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/branding/logo', [BrandingController::class, 'edit'])
        ->name('branding.logo.edit');

    Route::post('/branding/logo', [BrandingController::class, 'store'])
        ->name('branding.logo.store');
});

Route::get('/branding/logo/image', [BrandingController::class, 'image'])
    ->name('branding.logo.image');

/*
|--------------------------------------------------------------------------
| Per-school logo image (public; the token is not a database id)
|--------------------------------------------------------------------------
*/
Route::get('/school-logo/{token}', [SchoolLogoController::class, 'image'])
    ->name('schools.logo.image');

/*
|--------------------------------------------------------------------------
| Admission selection page (authenticated users only)
|--------------------------------------------------------------------------
*/
Route::get('/admission', function () {
    return redirect()->route('admin.schools.index');
})->middleware('auth')->name('admission.select');

/*
|--------------------------------------------------------------------------
| Public admission form workflow
|--------------------------------------------------------------------------
| The school is resolved from the token. Guests can open the form, preview,
| edit and submit an admission. The selection page above stays protected.
*/
Route::get('/admission/{token}', [AdmissionController::class, 'create'])
    ->name('admission.create');

Route::post('/admission/{token}/preview', [AdmissionController::class, 'preview'])
    ->middleware('throttle:20,1')
    ->name('admission.preview');

Route::get('/admission/{token}/edit', [AdmissionController::class, 'edit'])
    ->name('admission.edit');

Route::post('/admission/{token}/submit', [AdmissionController::class, 'submit'])
    ->middleware('throttle:20,1')
    ->name('admission.submit');

/*
|--------------------------------------------------------------------------
| Admin school management (authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/schools', [SchoolController::class, 'index'])
            ->name('schools.index');

        Route::post('/schools', [SchoolController::class, 'store'])
            ->name('schools.store');

        Route::get('/schools/{school}', [SchoolController::class, 'show'])
            ->name('schools.show');

        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])
            ->name('schools.edit');

        Route::put('/schools/{school}', [SchoolController::class, 'update'])
            ->name('schools.update');

        Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])
            ->name('schools.destroy');

        Route::get('/schools/{school}/logo', [SchoolLogoController::class, 'edit'])
            ->name('logo.edit');

        Route::post('/schools/{school}/logo', [SchoolLogoController::class, 'store'])
            ->name('logo.store');

        Route::get('/schools/{school}/classes', [ClassSectionController::class, 'index'])
            ->name('classes.index');

        Route::post('/schools/{school}/classes', [ClassSectionController::class, 'storeClass'])
            ->name('classes.store');

        Route::put('/schools/{school}/classes/{class}', [ClassSectionController::class, 'updateClass'])
            ->name('classes.update');

        Route::delete('/schools/{school}/classes/{class}', [ClassSectionController::class, 'destroyClass'])
            ->name('classes.destroy');

        Route::post('/schools/{school}/classes/{class}/sections', [ClassSectionController::class, 'storeSection'])
            ->name('sections.store');

        Route::put('/schools/{school}/classes/{class}/sections/{section}', [ClassSectionController::class, 'updateSection'])
            ->name('sections.update');

        Route::delete('/schools/{school}/classes/{class}/sections/{section}', [ClassSectionController::class, 'destroySection'])
            ->name('sections.destroy');

        Route::get('/schools/{school}/students', [StudentController::class, 'index'])
            ->name('students.index');

        Route::get('/schools/{school}/students/{student}', [StudentController::class, 'show'])
            ->name('students.show');

        Route::get('/schools/{school}/students/{student}/edit', [StudentController::class, 'edit'])
            ->name('students.edit');

        Route::put('/schools/{school}/students/{student}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/schools/{school}/students/{student}', [StudentController::class, 'destroy'])
            ->name('students.destroy');
    });
