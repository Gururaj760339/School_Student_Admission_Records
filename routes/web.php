
<?php

use App\Http\Controllers\Admin\ClassSectionController;
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
| School logo (upload is authenticated; the image itself is public)
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
| Admission selection page (authenticated users only)
|--------------------------------------------------------------------------
*/
Route::get('/admission', [AdmissionController::class, 'select'])
    ->middleware('auth')
    ->name('admission.select');

/*
|--------------------------------------------------------------------------
| Public admission form workflow
|--------------------------------------------------------------------------
| Guests can open the form, preview, edit and submit an admission.
| The selection page above remains protected by auth middleware.
*/
Route::get('/admission/{class}', [AdmissionController::class, 'create'])
    ->name('admission.create');

Route::post('/admission/{class}/preview', [AdmissionController::class, 'preview'])
    ->middleware('throttle:20,1')
    ->name('admission.preview');

Route::get('/admission/{class}/edit', [AdmissionController::class, 'edit'])
    ->name('admission.edit');

Route::post('/admission/{class}/submit', [AdmissionController::class, 'submit'])
    ->middleware('throttle:20,1')
    ->name('admission.submit');

/*
|--------------------------------------------------------------------------
| Admin student management (authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Class & section management
        |--------------------------------------------------------------------------
        | Register these routes before the student routes.
        */
        Route::get('/students/classes', [ClassSectionController::class, 'index'])
            ->name('classes.index');

        Route::post('/students/classes', [ClassSectionController::class, 'storeClass'])
            ->name('classes.store');

        Route::put('/students/classes/{class}', [ClassSectionController::class, 'updateClass'])
            ->name('classes.update');

        Route::delete('/students/classes/{class}', [ClassSectionController::class, 'destroyClass'])
            ->name('classes.destroy');

        Route::post('/students/classes/{class}/sections', [ClassSectionController::class, 'storeSection'])
            ->name('sections.store');

        Route::put('/students/sections/{section}', [ClassSectionController::class, 'updateSection'])
            ->name('sections.update');

        Route::delete('/students/sections/{section}', [ClassSectionController::class, 'destroySection'])
            ->name('sections.destroy');

        Route::post('/students/sections/{section}/sync', [ClassSectionController::class, 'sync'])
            ->name('sections.sync');

        /*
        |--------------------------------------------------------------------------
        | Student management
        |--------------------------------------------------------------------------
        */
        Route::get('/students', [StudentController::class, 'index'])
            ->name('students.index');

        Route::get('/students/{class}/{recordId}', [StudentController::class, 'show'])
            ->name('students.show');

        Route::get('/students/{class}/{recordId}/edit', [StudentController::class, 'edit'])
            ->name('students.edit');

        Route::put('/students/{class}/{recordId}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/students/{class}/{recordId}', [StudentController::class, 'destroy'])
            ->name('students.destroy');
    });
