@extends('layouts.app')

@section('title', 'Student Records')

@section('content')
    <div class="card">
        <div class="page-head">
            <h1>Unable to load student records</h1>
        </div>

        <div class="alert alert-error" role="alert">{{ $message }}</div>

        <p class="muted">
            The student records could not be loaded. Please return to school management and try again.
        </p>

        <div class="form-actions">
            <a class="btn btn-primary" href="{{ route('admin.schools.index') }}">Back to Schools</a>
        </div>
    </div>
@endsection
