@extends('layouts.app')

@section('title', 'Preview Admission · '.$school->name)

@section('content')
<style>
    .admission-preview-page {
        --ap-primary: #4f46e5;
        --ap-primary-hover: #4338ca;
        --ap-text: #111827;
        --ap-muted: #6b7280;
        --ap-border: #e5e7eb;
        --ap-surface: #ffffff;

        width: 100%;
        color: var(--ap-text);
        font-family: inherit;
    }

    .admission-preview-page *,
    .admission-preview-page *::before,
    .admission-preview-page *::after {
        box-sizing: border-box;
    }

    .admission-preview-page .ap-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .admission-preview-page .ap-heading {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .admission-preview-page .ap-heading-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        border-radius: 14px;
        background: #eef2ff;
        color: var(--ap-primary);
    }

    .admission-preview-page .ap-heading-icon svg {
        width: 27px;
        height: 27px;
    }

    .admission-preview-page .ap-header h1 {
        margin: 0 0 7px;
        color: var(--ap-text);
        font-size: clamp(23px, 3vw, 29px);
        font-weight: 750;
        letter-spacing: -.6px;
        line-height: 1.35;
    }

    .admission-preview-page .ap-subtitle {
        margin: 0;
        color: var(--ap-muted);
        font-size: 14px;
        line-height: 1.8;
    }

    .admission-preview-page .ap-class-name {
        color: var(--ap-primary);
        font-weight: 750;
    }

    .admission-preview-page .ap-panel {
        overflow: hidden;
        border: 1px solid var(--ap-border);
        border-radius: 16px;
        background: var(--ap-surface);
        box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
    }

    .admission-preview-page .ap-panel-header {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 21px 24px;
        border-bottom: 1px solid var(--ap-border);
        background: #fcfcfd;
    }

    .admission-preview-page .ap-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 11px;
        background: #eef2ff;
        color: var(--ap-primary);
    }

    .admission-preview-page .ap-panel-icon svg {
        width: 22px;
        height: 22px;
    }

    .admission-preview-page .ap-panel-header h2 {
        margin: 0 0 5px;
        color: var(--ap-text);
        font-size: 17px;
        font-weight: 750;
    }

    .admission-preview-page .ap-panel-header p {
        margin: 0;
        color: var(--ap-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .admission-preview-page .ap-panel-body {
        padding: 25px;
    }

    .admission-preview-page .ap-notice {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 22px;
        padding: 15px 16px;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        background: #eff6ff;
        color: #1e40af;
        font-size: 13px;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .admission-preview-page .ap-notice-error {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .admission-preview-page .ap-notice svg {
        width: 19px;
        height: 19px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .admission-preview-page .ap-intro {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin: 0 0 24px;
        padding: 15px 16px;
        border: 1px solid #d1fae5;
        border-radius: 10px;
        background: #f0fdf4;
        color: #166534;
        font-size: 13px;
        line-height: 1.8;
    }

    .admission-preview-page .ap-intro svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .admission-preview-page .ap-intro p {
        margin: 0;
    }

    .admission-preview-page .ap-details-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
    }

    .admission-preview-page .ap-details-heading h3 {
        margin: 0;
        color: var(--ap-text);
        font-size: 16px;
        font-weight: 750;
    }

    .admission-preview-page .ap-details-heading span {
        color: var(--ap-muted);
        font-size: 12px;
    }

    .admission-preview-page .ap-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 0;
    }

    .admission-preview-page .ap-detail-item {
        min-width: 0;
        padding: 16px 17px;
        border: 1px solid #e9ebf1;
        border-radius: 11px;
        background: #ffffff;
        transition: border-color .2s, background .2s;
    }

    .admission-preview-page .ap-detail-item:hover {
        border-color: #c7d2fe;
        background: #fafaff;
    }

    .admission-preview-page .ap-detail-item dt {
        margin: 0 0 8px;
        color: var(--ap-muted);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .admission-preview-page .ap-detail-item dd {
        margin: 0;
        color: #1f2937;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.8;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .admission-preview-page .ap-empty-value {
        color: #9ca3af;
        font-weight: 400;
    }

    .admission-preview-page .ap-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 26px;
        padding-top: 22px;
        border-top: 1px solid #eef0f4;
    }

    .admission-preview-page .ap-action-note {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--ap-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .admission-preview-page .ap-action-note svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    .admission-preview-page .ap-action-buttons {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .admission-preview-page .ap-action-buttons form {
        margin: 0;
    }

    .admission-preview-page .ap-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 9px;
        min-height: 43px;
        padding: 11px 17px;
        border: 1px solid transparent;
        border-radius: 9px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.5;
        text-decoration: none;
        cursor: pointer;
        transition: background .2s, border-color .2s, box-shadow .2s;
    }

    .admission-preview-page .ap-btn svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    .admission-preview-page .ap-btn-secondary {
        border-color: #d1d5db;
        background: #ffffff;
        color: #374151;
    }

    .admission-preview-page .ap-btn-secondary:hover {
        border-color: #a5b4fc;
        background: #eef2ff;
        color: var(--ap-primary-hover);
    }

    .admission-preview-page .ap-btn-primary {
        background: var(--ap-primary);
        color: #ffffff;
        box-shadow: 0 2px 5px rgba(79, 70, 229, .16);
    }

    .admission-preview-page .ap-btn-primary:hover {
        background: var(--ap-primary-hover);
        box-shadow: 0 4px 10px rgba(79, 70, 229, .2);
    }

    .admission-preview-page .ap-btn:focus-visible {
        outline: 3px solid rgba(79, 70, 229, .25);
        outline-offset: 2px;
    }

    @media (max-width: 700px) {
        .admission-preview-page .ap-header {
            align-items: flex-start;
        }

        .admission-preview-page .ap-heading-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }

        .admission-preview-page .ap-panel-header {
            padding: 17px;
        }

        .admission-preview-page .ap-panel-body {
            padding: 18px;
        }

        .admission-preview-page .ap-detail-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 11px;
        }

        .admission-preview-page .ap-detail-item {
            padding: 14px 15px;
        }

        .admission-preview-page .ap-actions {
            align-items: stretch;
        }

        .admission-preview-page .ap-action-note {
            width: 100%;
        }

        .admission-preview-page .ap-action-buttons {
            width: 100%;
        }

        .admission-preview-page .ap-action-buttons form {
            flex: 1;
        }

        .admission-preview-page .ap-action-buttons .ap-btn {
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .admission-preview-page .ap-action-buttons {
            flex-direction: column;
        }

        .admission-preview-page .ap-action-buttons form {
            width: 100%;
            flex: auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admission-preview-page *,
        .admission-preview-page *::before,
        .admission-preview-page *::after {
            transition: none !important;
        }
    }
</style>

<div class="admission-preview-page">

    {{-- Page Header --}}
    <div class="ap-header">
        <div class="ap-heading">
            <div class="ap-heading-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6"/>
                    <path d="m9 15 2 2 4-4"/>
                </svg>
            </div>

            <div>
                <h1>Review &amp; Confirm</h1>
                <p class="ap-subtitle">
                    {{ $school->name }}
                    <span aria-hidden="true">·</span>
                    <strong class="ap-class-name">
                        {{ $className ?? 'No class selected' }}@if ($sectionName) · {{ $sectionName }}@endif
                    </strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Main Preview Panel --}}
    <div class="ap-panel">

        <div class="ap-panel-header">
            <div class="ap-panel-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                </svg>
            </div>

            <div>
                <h2>Admission Details</h2>
                <p>Review the information carefully before submitting.</p>
            </div>
        </div>

        <div class="ap-panel-body">

            @if (! empty($error))
                <div class="ap-notice ap-notice-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01"/>
                    </svg>

                    <div>{{ $error }}</div>
                </div>
            @endif

            <div class="ap-intro">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 11v5M12 7h.01"/>
                </svg>

                <p>
                    Nothing has been saved yet. Please verify all the details below.
                    Click <strong>Confirm &amp; Submit</strong> when everything is correct
                    to save the record to the school database.
                </p>
            </div>

            <div class="ap-details-heading">
                <h3>Student Information</h3>
                <span>Review all entered details</span>
            </div>

            <dl class="detail-grid ap-detail-grid">
                <div class="detail-item ap-detail-item">
                    <dt>Class</dt>
                    <dd>{{ $className ?? '—' }}</dd>
                </div>

                <div class="detail-item ap-detail-item">
                    <dt>Section</dt>
                    <dd>{{ $sectionName ?? '—' }}</dd>
                </div>

                @foreach ($fields as $name => $field)
                    <div class="detail-item ap-detail-item">
                        <dt>{{ $field['label'] ?? $name }}</dt>
                        <dd>
                            @php $value = $record[$name] ?? null; @endphp

                            @if ($value === null || $value === '')
                                <span class="muted ap-empty-value">—</span>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>

            {{-- Action Buttons --}}
            <div class="form-actions preview-actions ap-actions">

                <div class="ap-action-note">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 11v5M12 7h.01"/>
                    </svg>
                    Please verify the details before final submission.
                </div>

                <div class="ap-action-buttons">

                    <form method="GET"
                          action="{{ route('admission.edit', ['token' => $school->admission_token]) }}"
                          class="inline">

                        <button type="submit" class="ap-btn ap-btn-secondary">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="m15 18-6-6 6-6"/>
                                <path d="M9 12h12"/>
                            </svg>
                            Edit Details
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('admission.submit', ['token' => $school->admission_token]) }}"
                          class="inline"
                          data-single-submit>

                        @csrf

                        <input type="hidden" name="submission_token" value="{{ $token }}">

                        <button type="submit" class="ap-btn ap-btn-primary">
                            Confirm &amp; Submit
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection