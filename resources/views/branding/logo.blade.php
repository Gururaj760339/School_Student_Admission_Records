@extends('layouts.app')

@section('title', 'Upload Logo')

@section('content')
    <div class="card card-narrow">
        <div class="page-head">
            <h1>School Logo</h1>
            <p class="muted">
                Upload a logo to display on the admission form.
                Accepted formats: PNG, JPG, JPEG and WEBP (maximum 2&nbsp;MB).
            </p>
        </div>

        @if ($logoUrl)
            <div class="form-field">
                <label>Current logo</label>
                <img src="{{ $logoUrl }}"
                     alt="Current school logo"
                     style="max-width:180px;max-height:120px;">
            </div>
        @endif

        <form method="POST"
              action="{{ route('branding.logo.store') }}"
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
            </div>
        </form>
    </div>
@endsection
