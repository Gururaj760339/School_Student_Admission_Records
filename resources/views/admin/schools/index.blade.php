@extends('layouts.app')

@section('title', 'School Management')

@section('content')
    <div class="card">
        <div class="page-head">
            <div>
                <h1>School Management</h1>
                <p class="muted">Create and manage schools, each with its own admission link, classes and students.</p>
            </div>

            <div class="head-actions">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Log out</button>
                </form>
            </div>
        </div>

        @if ($schools->isEmpty())
            <div class="alert alert-info">
                No schools have been created yet. Use the form below to create the first school.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Classes</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schools as $school)
                            <tr>
                                <td>
                                    <strong>{{ $school->name }}</strong>
                                    <div class="muted small">{{ $school->admissionUrl() }}</div>
                                </td>
                                <td>{{ $school->classes_count }}</td>
                                <td>{{ $school->students_count }}</td>
                                <td>
                                    <div class="head-actions">
                                        <a class="btn btn-small btn-primary" href="{{ route('admin.schools.show', $school) }}">
                                            View Dashboard
                                        </a>
                                        <a class="btn btn-small btn-ghost" href="{{ route('admin.schools.edit', $school) }}">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn btn-small btn-danger"
                                                data-confirm="Delete {{ $school->name }}? This cannot be undone."
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h2>Create School</h2>

        <form method="POST" action="{{ route('admin.schools.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-field {{ $errors->has('name') ? 'has-error' : '' }}">
                    <label for="school-name">School Name <span class="req">*</span></label>
                    <input
                        type="text"
                        id="school-name"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="255"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create School</button>
            </div>
        </form>
    </div>
@endsection
