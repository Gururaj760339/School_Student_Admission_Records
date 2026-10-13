@extends('layouts.app')

@section('title', 'Google Sheets Configuration Error')

@section('content')
    <div class="card">
        <div class="page-head">
            <h1>Google Sheets error</h1>
            @if (! empty($isSetupError))
                <p class="muted">This looks like a configuration or access problem that an administrator must fix.</p>
            @endif
        </div>

        <div class="alert alert-error" role="alert">{{ $message }}</div>

        <div class="setup-help">
            <h2>Checklist</h2>
            <ol>
                <li>Place the service-account JSON at
                    <code>storage/app/google/service-account.json</code> (outside the public web directory).</li>
                <li>Share the spreadsheet with the service-account email as <strong>Editor</strong>.</li>
                <li>Enable the Google Sheets API in the Google Cloud project.</li>
                <li>Confirm each class/section has a worksheet tab named <code>Class - Section</code>
                    (created automatically from the admin panel).</li>
                <li>Ensure the credential key has not been revoked or expired.</li>
            </ol>
            <p class="muted small">
                Private keys are never displayed here. If the key was exposed, revoke it in Google Cloud and
                replace the local file.
            </p>
        </div>

        <div class="form-actions">
            <a class="btn btn-primary" href="{{ route('admission.select') }}">Back to admissions</a>
        </div>
    </div>
@endsection
