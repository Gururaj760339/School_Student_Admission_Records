<?php

use Google\Service\Sheets;

return [

    /*
    |--------------------------------------------------------------------------
    | Google Cloud Project
    |--------------------------------------------------------------------------
    |
    | Informational only. It is never required to authenticate, but keeping it
    | here makes the expected project explicit for operators.
    |
    */

    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'school-student-records'),

    /*
    |--------------------------------------------------------------------------
    | Service Account
    |--------------------------------------------------------------------------
    |
    | The service account email must be granted Editor access to the target
    | spreadsheet. The private key is read ONLY from the local JSON file below.
    | It is never stored in config, source code, views, or logs.
    |
    */

    'service_account_email' => env(
        'GOOGLE_SERVICE_ACCOUNT_EMAIL',
        'school-student-admission-recor@school-student-records.iam.gserviceaccount.com'
    ),

    /*
    |--------------------------------------------------------------------------
    | Spreadsheet
    |--------------------------------------------------------------------------
    |
    | Student records live exclusively in this spreadsheet. Each class maps to
    | its own worksheet (tab) inside it.
    |
    */

    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),

    /*
    |--------------------------------------------------------------------------
    | Credentials Path
    |--------------------------------------------------------------------------
    |
    | A path relative to the Laravel base path (e.g. "storage/app/..."). It is
    | resolved with storage_path()/base_path() at runtime and must never live
    | inside the public web directory.
    |
    */

    'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH', 'storage/app/google/service-account.json'),

    'application_name' => env('GOOGLE_SHEETS_APPLICATION_NAME', 'School Student Admission Records'),

    /*
    |--------------------------------------------------------------------------
    | API Access
    |--------------------------------------------------------------------------
    */

    'scopes' => [
        Sheets::SPREADSHEETS,
    ],

    // Seconds to wait for the Google API before aborting a request.
    'request_timeout' => (int) env('GOOGLE_SHEETS_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Class
    |--------------------------------------------------------------------------
    */

    'default_class' => env('GOOGLE_SHEETS_DEFAULT_CLASS', 'Class 1'),

    /*
    |--------------------------------------------------------------------------
    | Legacy Classes (fallback only)
    |--------------------------------------------------------------------------
    |
    | Classes and sections are now managed dynamically from the admin panel
    | (/admin/students/classes) and stored in the database; each section maps
    | to a worksheet named "<Class> - <Section>".
    |
    | This array is a LEGACY FALLBACK for installations that have not yet
    | created any database class. It is ignored entirely as soon as at least
    | one class exists in the database, and can be emptied after migrating.
    |
    */

    'classes' => [
        'class_1' => [
            'name' => 'Class 1',
            'worksheet' => 'Class 1',
        ],
        'class_2' => [
            'name' => 'Class 2',
            'worksheet' => 'Class 2',
        ],
    ],

];
