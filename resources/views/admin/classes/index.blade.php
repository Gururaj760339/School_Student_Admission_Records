
@extends('layouts.app')

@section('title', 'Manage Classes & Sections')

@section('content')
<style>
    .classes-page {
        --cs-primary: #4f46e5;
        --cs-primary-hover: #4338ca;
        --cs-text: #111827;
        --cs-muted: #6b7280;
        --cs-border: #e5e7eb;
        --cs-surface: #ffffff;
        --cs-bg: #f8fafc;
        color: var(--cs-text);
        width: 100%;
        font-family: inherit;
    }

    .classes-page *,
    .classes-page *::before,
    .classes-page *::after {
        box-sizing: border-box;
    }

    .classes-page .cs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .classes-page .cs-heading {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .classes-page .cs-heading-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        border-radius: 14px;
        background: #eef2ff;
        color: var(--cs-primary);
    }

    .classes-page .cs-heading-icon svg {
        width: 27px;
        height: 27px;
    }

    .classes-page .cs-header h1 {
        margin: 0 0 6px;
        color: var(--cs-text);
        font-size: clamp(22px, 3vw, 28px);
        font-weight: 750;
        letter-spacing: -0.7px;
        line-height: 1.3;
    }

    .classes-page .cs-subtitle {
        margin: 0;
        color: var(--cs-muted);
        font-size: 14px;
        line-height: 1.7;
    }

    .classes-page .cs-card {
        margin-bottom: 24px;
        overflow: hidden;
        background: var(--cs-surface);
        border: 1px solid var(--cs-border);
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035);
    }

    .classes-page .cs-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--cs-border);
        background: #fcfcfd;
    }

    .classes-page .cs-section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 11px;
        background: #eef2ff;
        color: var(--cs-primary);
    }

    .classes-page .cs-section-icon svg {
        width: 21px;
        height: 21px;
    }

    .classes-page .cs-card-header h2 {
        margin: 0 0 4px;
        color: var(--cs-text);
        font-size: 17px;
        font-weight: 700;
    }

    .classes-page .cs-card-header p {
        margin: 0;
        color: var(--cs-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .classes-page .cs-card-body {
        padding: 24px;
    }

    .classes-page .cs-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .classes-page .cs-field {
        min-width: 0;
    }

    .classes-page .cs-label {
        display: block;
        margin-bottom: 9px;
        color: #374151;
        font-size: 13px;
        font-weight: 650;
    }

    .classes-page .cs-required {
        color: #dc2626;
        margin-left: 3px;
    }

    .classes-page .cs-input-wrap {
        position: relative;
    }

    .classes-page .cs-input-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    .classes-page .cs-input-icon svg {
        width: 19px;
        height: 19px;
    }

    .classes-page .cs-input {
        display: block;
        width: 100%;
        min-width: 0;
        height: 48px;
        margin: 0;
        padding: 0 14px 0 43px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        outline: none;
        background: #ffffff;
        color: var(--cs-text);
        font-family: inherit;
        font-size: 14px;
        transition: border-color .2s, box-shadow .2s;
    }

    .classes-page .cs-input::placeholder {
        color: #9ca3af;
    }

    .classes-page .cs-input:hover {
        border-color: #9ca3af;
    }

    .classes-page .cs-input:focus {
        border-color: var(--cs-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
    }

    .classes-page .cs-help {
        margin: 8px 0 0;
        color: var(--cs-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .classes-page .cs-form-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 26px;
        padding-top: 22px;
        border-top: 1px solid #f0f1f4;
    }

    .classes-page .cs-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        padding: 10px 17px;
        border: 1px solid transparent;
        border-radius: 9px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.4;
        text-decoration: none;
        cursor: pointer;
        transition: background .2s, border-color .2s, box-shadow .2s;
    }

    .classes-page .cs-btn svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    .classes-page .cs-btn-primary {
        background: var(--cs-primary);
        color: #ffffff;
        box-shadow: 0 2px 5px rgba(79, 70, 229, .18);
    }

    .classes-page .cs-btn-primary:hover:not(:disabled) {
        background: var(--cs-primary-hover);
        box-shadow: 0 4px 10px rgba(79, 70, 229, .22);
    }

    .classes-page .cs-btn:focus-visible {
        outline: 3px solid rgba(79, 70, 229, .25);
        outline-offset: 2px;
    }

    .classes-page .cs-btn:disabled {
        cursor: wait;
        opacity: .72;
    }

    .classes-page .cs-btn-secondary {
        min-height: 35px;
        padding: 7px 11px;
        border-color: #dbe3f0;
        background: #ffffff;
        color: #374151;
        font-size: 12px;
    }

    .classes-page .cs-btn-secondary:hover {
        border-color: #a5b4fc;
        background: #eef2ff;
        color: var(--cs-primary);
    }

    .classes-page .cs-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
        padding: 14px 16px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .classes-page .cs-alert svg {
        width: 19px;
        height: 19px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .classes-page .cs-alert-warning {
        border-color: #fde68a;
        background: #fffbeb;
        color: #92400e;
    }

    .classes-page .cs-alert-danger {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .classes-page .cs-alert ul {
        margin: 0;
        padding-left: 18px;
    }

    .classes-page .cs-alert li + li {
        margin-top: 4px;
    }

    .classes-page .cs-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .classes-page .cs-list-header h2 {
        margin: 0 0 5px;
        color: var(--cs-text);
        font-size: 19px;
        font-weight: 750;
        letter-spacing: -.3px;
    }

    .classes-page .cs-list-header p {
        margin: 0;
        color: var(--cs-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .classes-page .cs-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 30px;
        padding: 0 10px;
        border: 1px solid #e0e7ff;
        border-radius: 8px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 750;
    }

    .classes-page .cs-class-panel {
        margin-top: 14px;
        overflow: hidden;
        border: 1px solid var(--cs-border);
        border-radius: 12px;
        background: #ffffff;
    }

    .classes-page .cs-class-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 18px;
        background: #f9fafb;
        border-bottom: 1px solid var(--cs-border);
    }

    .classes-page .cs-class-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        background: #e0e7ff;
        color: #4338ca;
        font-size: 15px;
        font-weight: 750;
    }

    .classes-page .cs-class-heading h3 {
        margin: 0;
        color: #1f2937;
        font-size: 15px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .classes-page .cs-class-heading p {
        margin: 4px 0 0;
        color: var(--cs-muted);
        font-size: 12px;
    }

    .classes-page .cs-section-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 16px;
    }

    .classes-page .cs-section-item {
        min-width: 0;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        transition: border-color .2s, box-shadow .2s;
    }

    .classes-page .cs-section-item:hover {
        border-color: #c7d2fe;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .04);
    }

    .classes-page .cs-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
    }

    .classes-page .cs-section-letter {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 33px;
        height: 33px;
        flex-shrink: 0;
        border-radius: 9px;
        background: #f3f4f6;
        color: #374151;
        font-size: 13px;
        font-weight: 750;
    }

    .classes-page .cs-section-title strong {
        display: block;
        margin-bottom: 3px;
        color: #374151;
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    .classes-page .cs-section-title small {
        color: var(--cs-muted);
        font-size: 11px;
    }

    .classes-page .cs-meta-label {
        display: block;
        margin-bottom: 7px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 650;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .classes-page .cs-worksheet {
        display: block;
        width: 100%;
        padding: 9px 10px;
        overflow-wrap: anywhere;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        background: #f9fafb;
        color: #374151;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px;
        line-height: 1.6;
        white-space: normal;
    }

    .classes-page .cs-sync-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 14px;
    }

    .classes-page .cs-sync-label {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
    }

    .classes-page .cs-sync-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
    }

    .classes-page .cs-sync-dot {
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: currentColor;
    }

    .classes-page .cs-sync-synced {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .classes-page .cs-sync-failed {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .classes-page .cs-sync-pending {
        border-color: #fde68a;
        background: #fffbeb;
        color: #a16207;
    }

    .classes-page .cs-sync-error {
        margin: 10px 0 0;
        padding: 10px;
        border-radius: 7px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 12px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .classes-page .cs-retry-form {
        margin-top: 13px;
        padding-top: 12px;
        border-top: 1px solid #f0f1f4;
    }

    .classes-page .cs-empty {
        padding: 38px 20px;
        text-align: center;
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        background: #fafbfc;
    }

    .classes-page .cs-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin: 0 auto 14px;
        border-radius: 15px;
        background: #eef2ff;
        color: var(--cs-primary);
    }

    .classes-page .cs-empty-icon svg {
        width: 26px;
        height: 26px;
    }

    .classes-page .cs-empty h3 {
        margin: 0 0 7px;
        color: #374151;
        font-size: 15px;
        font-weight: 700;
    }

    .classes-page .cs-empty p {
        margin: 0;
        color: var(--cs-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    @media (max-width: 700px) {
        .classes-page .cs-header {
            align-items: flex-start;
        }

        .classes-page .cs-heading {
            align-items: flex-start;
        }

        .classes-page .cs-heading-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }

        .classes-page .cs-card-header {
            padding: 17px;
        }

        .classes-page .cs-card-body {
            padding: 17px;
        }

        .classes-page .cs-form-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
        }

        .classes-page .cs-form-footer {
            margin-top: 20px;
            padding-top: 18px;
        }

        .classes-page .cs-form-footer .cs-btn {
            width: 100%;
        }

        .classes-page .cs-section-list {
            grid-template-columns: minmax(0, 1fr);
            padding: 12px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .classes-page *,
        .classes-page *::before,
        .classes-page *::after {
            transition: none !important;
        }
    }
</style>

<div class="classes-page">

    {{-- Page Header --}}
    <div class="cs-header">
        <div class="cs-heading">
            <div class="cs-heading-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"/>
                    <path d="M5 21V7l8-4v18"/>
                    <path d="M19 21V11l-6-4"/>
                    <path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
                    <path d="M16 13v.01M16 16v.01M16 19v.01"/>
                </svg>
            </div>

            <div>
                <h1>Manage Classes &amp; Sections</h1>
                <p class="cs-subtitle">
                    Organize your academic classes and manage their sections
                    with Google Sheets synchronization.
                </p>
            </div>
        </div>
    </div>

    {{-- Warning Message --}}
    @if (session('warning'))
        <div class="cs-alert cs-alert-warning" role="alert">
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
                <path d="M12 9v4"/>
                <path d="M12 17h.01"/>
                <path d="M10.3 3.9 2.5 17.4A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
            </svg>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="cs-alert cs-alert-danger" role="alert">
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 8v5"/>
                <path d="M12 16h.01"/>
            </svg>

            <div>
                <strong>Please correct the following errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Create Class and Section --}}
    <div class="cs-card">
        <div class="cs-card-header">
            <div class="cs-section-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="8" r="4"/>
                    <path d="M3 21v-2a6 6 0 0 1 12 0v2"/>
                    <path d="M19 8v6M16 11h6"/>
                </svg>
            </div>

            <div>
                <h2>Add New Class &amp; Section</h2>
                <p>
                    Enter the class and section details below to create a record.
                </p>
            </div>
        </div>

        <div class="cs-card-body">
            <form
                method="POST"
                action="{{ route('admin.classes.store') }}"
                id="class-section-form"
            >
                @csrf

                <div class="cs-form-grid">
                    <div class="cs-field">
                        <label class="cs-label" for="class-name">
                            Class Name
                            <span class="cs-required">*</span>
                        </label>

                        <div class="cs-input-wrap">
                            <span class="cs-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                    <path d="M8 7h8M8 11h6"/>
                                </svg>
                            </span>

                            <input
                                type="text"
                                class="cs-input"
                                id="class-name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="e.g. Class 1"
                                maxlength="100"
                                autocomplete="off"
                                required
                            >
                        </div>

                        <p class="cs-help">
                            Enter the name of the academic class.
                        </p>
                    </div>

                    <div class="cs-field">
                        <label class="cs-label" for="section-name">
                            Section Name
                            <span class="cs-required">*</span>
                        </label>

                        <div class="cs-input-wrap">
                            <span class="cs-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                </svg>
                            </span>

                            <input
                                type="text"
                                class="cs-input"
                                id="section-name"
                                name="section_name"
                                value="{{ old('section_name') }}"
                                placeholder="e.g. A"
                                maxlength="100"
                                autocomplete="off"
                                required
                            >
                        </div>

                        <p class="cs-help">
                            Enter the section name, such as A, B or C.
                        </p>
                    </div>
                </div>

                <div class="cs-form-footer">
                    <button
                        type="submit"
                        class="cs-btn cs-btn-primary"
                        id="submit-class-section"
                    >
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <span id="submit-class-section-text">
                            Save Class &amp; Section
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- All Classes and Sections --}}
    <div class="cs-list-header">
        <div>
            <h2>All Class &amp; Section Data</h2>
            <p>
                View registered classes, their sections and Google Sheets sync status.
            </p>
        </div>

        <span class="cs-count" title="Total registered classes">
            {{ $classes->count() }} {{ \Illuminate\Support\Str::plural('Class', $classes->count()) }}
        </span>
    </div>

    @forelse ($classes as $class)
        <div class="cs-class-panel">

            {{-- Class Heading --}}
            <div class="cs-class-heading">
                <div class="cs-class-avatar" aria-hidden="true">
                    {{ \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::substr($class->name, 0, 1)
                    ) }}
                </div>

                <div>
                    <h3>{{ $class->name }}</h3>
                    <p>
                        {{ $class->sections->count() }}
                        {{ \Illuminate\Support\Str::plural(
                            'section',
                            $class->sections->count()
                        ) }}
                        registered
                    </p>
                </div>
            </div>

            @forelse ($class->sections as $section)
                @if ($loop->first)
                    <div class="cs-section-list">
                @endif

                <div class="cs-section-item">
                    {{-- Section Heading --}}
                    <div class="cs-section-title">
                        <div class="cs-section-letter" aria-hidden="true">
                            {{ \Illuminate\Support\Str::upper(
                                \Illuminate\Support\Str::substr($section->name, 0, 1)
                            ) }}
                        </div>

                        <div>
                            <strong>Section {{ $section->name }}</strong>
                            <small>Class section</small>
                        </div>
                    </div>

                    {{-- Worksheet Name --}}
                    <div>
                        <span class="cs-meta-label">Google Sheets Worksheet</span>

                        <code class="cs-worksheet">{{ $section->worksheetName() }}</code>
                    </div>

                    {{-- Sync Status --}}
                    <div class="cs-sync-row">
                        <span class="cs-sync-label">Sync Status</span>

                        @if ($section->google_sync_status === \App\Models\Section::SYNC_SYNCED)
                            <span class="cs-sync-badge cs-sync-synced">
                                <span class="cs-sync-dot"></span>
                                Synced
                            </span>
                        @elseif ($section->google_sync_status === \App\Models\Section::SYNC_FAILED)
                            <span class="cs-sync-badge cs-sync-failed">
                                <span class="cs-sync-dot"></span>
                                Failed
                            </span>
                        @else
                            <span class="cs-sync-badge cs-sync-pending">
                                <span class="cs-sync-dot"></span>
                                Pending
                            </span>
                        @endif
                    </div>

                    {{-- Sync Error --}}
                    @if (
                        $section->google_sync_status === \App\Models\Section::SYNC_FAILED
                        && $section->google_sync_error
                    )
                        <p class="cs-sync-error">
                            {{ $section->google_sync_error }}
                        </p>
                    @endif

                    {{-- Retry Google Sheets Synchronization --}}
                    @if ($section->google_sync_status !== \App\Models\Section::SYNC_SYNCED)
                        <form
                            method="POST"
                            action="{{ route('admin.sections.sync', ['section' => $section]) }}"
                            class="cs-retry-form"
                        >
                            @csrf

                            <button type="submit" class="cs-btn cs-btn-secondary">
                                <svg viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true">
                                    <path d="M20 7v5h-5"/>
                                    <path d="M4 17v-5h5"/>
                                    <path d="M5.6 9a7 7 0 0 1 11.5-2.5L20 12"/>
                                    <path d="M4 12l2.9 5.5A7 7 0 0 0 18.4 15"/>
                                </svg>
                                Retry Sync
                            </button>
                        </form>
                    @endif
                </div>

                @if ($loop->last)
                    </div>
                @endif
            @empty
                <div style="padding: 20px;">
                    <p class="cs-help" style="margin: 0;">
                        No sections have been added to this class yet.
                    </p>
                </div>
            @endforelse

        </div>
    @empty
        <div class="cs-empty">
            <div class="cs-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"/>
                    <path d="M5 21V7l8-4v18"/>
                    <path d="M19 21V11l-6-4"/>
                    <path d="M9 9v.01M9 12v.01M9 15v.01"/>
                </svg>
            </div>

            <h3>No Classes Found</h3>
            <p>
                There are no classes or sections registered yet.
                Use the form above to add your first class and section.
            </p>
        </div>
    @endforelse

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('class-section-form');
    const button = document.getElementById('submit-class-section');
    const buttonText = document.getElementById('submit-class-section-text');

    if (!form || !button || !buttonText) {
        return;
    }

    let isSubmitting = false;

    form.addEventListener('submit', function (event) {
        // Keep native browser validation.
        if (!form.checkValidity()) {
            return;
        }

        // Prevent duplicate submissions.
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        isSubmitting = true;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        buttonText.textContent = 'Saving...';
    });
});
</script>
@endsection
