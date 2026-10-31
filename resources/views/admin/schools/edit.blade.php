@extends('layouts.app')

@section('title', 'Edit '.$school->name)

@section('content')
    <div class="card card-narrow">
        <div class="page-head">
            <div>
                <h1>Edit School</h1>
                <p class="muted">Update the school name.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.schools.update', $school) }}">
            @csrf
            @method('PUT')

            <div class="form-field {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="school-name">School Name <span class="req">*</span></label>
                <input
                    type="text"
                    id="school-name"
                    name="name"
                    value="{{ old('name', $school->name) }}"
                    maxlength="255"
                    required
                    autofocus
                >
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a class="btn btn-ghost" href="{{ route('admin.schools.show', $school) }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
