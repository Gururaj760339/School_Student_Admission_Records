@extends('layouts.app')

@section('title', $school->name.' Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $school->name }}</h1>
            <p class="muted">School dashboard</p>
        </div>

        <div class="head-actions">
            <a class="btn btn-ghost" href="{{ route('admin.schools.index') }}">Back to School Management</a>
            <a class="btn btn-ghost" href="{{ route('admin.schools.edit', $school) }}">Edit School</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost">Log out</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Public Admission Link</h2>
        <p class="muted">Share this link with applicants. It opens only this school's admission form.</p>

        <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center;">
            <input
                type="text"
                id="admission-url"
                value="{{ $school->admissionUrl() }}"
                readonly
                onclick="this.select()"
                style="flex: 1 1 320px;"
            >
            <button type="button" class="btn btn-primary" id="copy-admission-url">Copy Link</button>
        </div>

        <p class="muted small" style="margin-bottom: 0;">
            {{ $school->classes_count }} {{ $school->classes_count === 1 ? 'class' : 'classes' }} ·
            {{ $school->students_count }} {{ $school->students_count === 1 ? 'student' : 'students' }}
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-top: 1rem;">
        <div class="card">
            <h2 style="margin-top: 0;">New Admission</h2>
            <p class="muted">Open the public admission form and submit a new student record.</p>
            <a
                class="btn btn-primary"
                href="{{ route('admission.create', ['token' => $school->admission_token]) }}"
                target="_blank"
                rel="noopener"
            >
                Open Admission Form
            </a>
        </div>

        <div class="card">
            <h2 style="margin-top: 0;">Manage Students</h2>
            <p class="muted">Search, review, edit and delete this school's student records.</p>
            <a class="btn btn-primary" href="{{ route('admin.students.index', $school) }}">Manage Students</a>
        </div>

        <div class="card">
            <h2 style="margin-top: 0;">Class and Section</h2>
            <p class="muted">Create, rename and remove classes and sections for this school.</p>
            <a class="btn btn-primary" href="{{ route('admin.classes.index', $school) }}">Manage Classes &amp; Sections</a>
        </div>

        <div class="card">
            <h2 style="margin-top: 0;">Upload Logo</h2>
            <p class="muted">Upload a logo that appears on this school's admission form.</p>

            <img
                src="{{ $school->logoUrl() ?: asset('images/logo.png') }}"
                alt="{{ $school->name }} logo"
                style="display: block; max-height: 64px; max-width: 100%; margin-bottom: 0.8rem;"
            >

            <a class="btn btn-primary" href="{{ route('admin.logo.edit', $school) }}">Upload Logo</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const copyButton = document.getElementById('copy-admission-url');
            const input = document.getElementById('admission-url');

            if (!copyButton || !input) return;

            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(input.value);
                } catch (error) {
                    input.select();
                    document.execCommand('copy');
                }

                const original = copyButton.textContent;
                copyButton.textContent = 'Copied';

                setTimeout(function () {
                    copyButton.textContent = original;
                }, 1600);
            });
        });
    </script>
@endpush
