@extends('layouts.app')

@section('title', isset($school) ? 'Upload Logo · '.$school->name : 'Upload Logo')

@section('content')
    <div class="card card-narrow">
        <div class="page-head">
            <div>
                <h1>{{ isset($school) ? $school->name.' Logo' : 'School Logo' }}</h1>
                <p class="muted">
                    Upload a logo to display on the admission form.
                    Accepted formats: PNG, JPG, JPEG and WEBP (maximum 2&nbsp;MB).
                </p>
            </div>
        </div>

        <div class="form-field">
            <label>Current logo</label>
            <img src="{{ $logoUrl }}"
                 alt="{{ isset($school) ? $school->name.' logo' : 'School logo' }}"
                 style="max-width:180px;max-height:120px;">
        </div>

        <form method="POST"
              action="{{ isset($school) ? route('admin.logo.store', $school) : route('branding.logo.store') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="form-field {{ $errors->has('logo') ? 'has-error' : '' }}">
                <label for="logo">Logo image</label>
                <input id="logo"
                       type="file"
                       name="logo"
                       accept="image/png,image/jpeg,image/webp"
                       required>
                @error('logo')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload Logo</button>

                @isset($school)
                    <a class="btn btn-ghost" href="{{ route('admin.schools.show', $school) }}">Back to Dashboard</a>
                @endisset
            </div>
        </form>
    </div>
@endsection
