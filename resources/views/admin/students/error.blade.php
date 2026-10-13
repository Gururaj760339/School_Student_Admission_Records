@extends('layouts.app')

@section('title', 'Student Records')

@section('content')
    <div class="card">
        <div class="page-head">
            <h1>Unable to load student records</h1>
        </div>

        <div class="alert alert-error" role="alert">{{ $message }}</div>

        <p class="muted">
            This is usually a Google Sheets configuration or access issue. Check the spreadsheet sharing,
            worksheet names, and the service-account credentials file, then try again.
        </p>

        <div class="form-actions">
            <a class="btn btn-primary" href="{{ route('admin.students.index') }}">Retry</a>
            <a class="btn btn-ghost" href="{{ route('admission.select') }}">New Admission</a>
        </div>
    </div>
@endsection
