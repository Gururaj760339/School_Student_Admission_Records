@extends('layouts.app')

@section('title', 'Admission Submitted')

@section('content')
<style>
    .admission-success-page {
        --as-primary: #4f46e5;
        --as-primary-hover: #4338ca;
        --as-text: #111827;
        --as-muted: #6b7280;
        --as-border: #e5e7eb;

        width: 100%;
        color: var(--as-text);
        font-family: inherit;
    }

    .admission-success-page *,
    .admission-success-page *::before,
    .admission-success-page *::after {
        box-sizing: border-box;
    }

    .admission-success-page .as-container {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }

    .admission-success-page .as-card {
        overflow: hidden;
        border: 1px solid var(--as-border);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .05);
    }

    .admission-success-page .as-success-banner {
        display: flex;
        align-items: flex-start;
        gap: 18px;
        padding: 30px;
        border-bottom: 1px solid #bbf7d0;
        background: linear-gradient(135deg, #f0fdf4 0%, #f7fff9 100%);
    }

    .admission-success-page .as-success-check {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        flex-shrink: 0;
        border: 1px solid #bbf7d0;
        border-radius: 17px;
        background: #dcfce7;
        color: #15803d;
        font-size: 31px;
        font-weight: 800;
        box-shadow: 0 3px 8px rgba(22, 163, 74, .08);
    }

    .admission-success-page .as-success-content {
        min-width: 0;
        padding-top: 2px;
    }

    .admission-success-page .as-status-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
        color: #15803d;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .admission-success-page .as-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
    }

    .admission-success-page .as-success-banner h1 {
        margin: 0 0 9px;
        color: #14532d;
        font-size: clamp(23px, 3vw, 30px);
        font-weight: 800;
        letter-spacing: -.6px;
        line-height: 1.35;
    }

    .admission-success-page .as-success-banner p {
        margin: 0;
        color: #4b6353;
        font-size: 14px;
        line-height: 1.9;
        overflow-wrap: anywhere;
    }

    .admission-success-page .as-success-banner strong {
        color: #166534;
        font-weight: 750;
    }

    .admission-success-page .as-body {
        padding: 28px 30px 30px;
    }

    .admission-success-page .as-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .admission-success-page .as-section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 12px;
        background: #eef2ff;
        color: var(--as-primary);
    }

    .admission-success-page .as-section-icon svg {
        width: 22px;
        height: 22px;
    }

    .admission-success-page .as-section-heading h2 {
        margin: 0 0 4px;
        color: var(--as-text);
        font-size: 17px;
        font-weight: 750;
    }

    .admission-success-page .as-section-heading p {
        margin: 0;
        color: var(--as-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .admission-success-page .as-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 0;
    }

    .admission-success-page .as-detail-item {
        min-width: 0;
        padding: 17px 18px;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        background: #ffffff;
        transition: border-color .2s, background .2s;
    }

    .admission-success-page .as-detail-item:hover {
        border-color: #c7d2fe;
        background: #fafaff;
    }

    .admission-success-page .as-detail-item dt {
        margin: 0 0 9px;
        color: var(--as-muted);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.6;
    }

    .admission-success-page .as-detail-item dd {
        margin: 0;
        color: #1f2937;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.8;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .admission-success-page .as-record-id {
        display: inline-block;
        max-width: 100%;
        padding: 5px 9px;
        border: 1px solid #e0e7ff;
        border-radius: 6px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    .admission-success-page .as-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
        padding-top: 23px;
        border-top: 1px solid #eef0f4;
    }

    .admission-success-page .as-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 44px;
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

    .admission-success-page .as-btn svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    .admission-success-page .as-btn-primary {
        background: var(--as-primary);
        color: #ffffff;
        box-shadow: 0 2px 5px rgba(79, 70, 229, .16);
    }

    .admission-success-page .as-btn-primary:hover {
        background: var(--as-primary-hover);
        box-shadow: 0 4px 10px rgba(79, 70, 229, .2);
    }

    .admission-success-page .as-btn-secondary {
        border-color: #d1d5db;
        background: #ffffff;
        color: #374151;
    }

    .admission-success-page .as-btn-secondary:hover {
        border-color: #a5b4fc;
        background: #eef2ff;
        color: var(--as-primary-hover);
    }

    .admission-success-page .as-btn:focus-visible {
        outline: 3px solid rgba(79, 70, 229, .25);
        outline-offset: 2px;
    }

    .admission-success-page .as-footer-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 21px;
        color: var(--as-muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .admission-success-page .as-footer-note svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    @media (max-width: 600px) {
        .admission-success-page .as-success-banner {
            gap: 14px;
            padding: 23px 19px;
        }

        .admission-success-page .as-success-check {
            width: 46px;
            height: 46px;
            border-radius: 13px;
            font-size: 25px;
        }

        .admission-success-page .as-body {
            padding: 22px 18px;
        }

        .admission-success-page .as-detail-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 11px;
        }

        .admission-success-page .as-detail-item {
            padding: 15px;
        }

        .admission-success-page .as-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .admission-success-page .as-btn {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admission-success-page *,
        .admission-success-page *::before,
        .admission-success-page *::after {
            transition: none !important;
        }
    }
</style>

<div class="admission-success-page">
    <div class="as-container">
        <div class="card as-card">

            {{-- Success Banner --}}
            <div class="success-banner as-success-banner" role="status">
                <span class="success-check as-success-check" aria-hidden="true">✓</span>

                <div class="as-success-content">
                    <div class="as-status-label">
                        <span class="as-status-dot"></span>
                        Successfully Completed
                    </div>

                    <h1>Admission Submitted</h1>

                    <p class="muted">
                        The record was written to the
                        <strong>{{ $className }}</strong>
                        worksheet and confirmed by Google Sheets.
                    </p>
                </div>
            </div>

            <div class="as-body">

                {{-- Record Details Heading --}}
                <div class="as-section-heading">
                    <div class="as-section-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                            <path d="M8 13h8M8 17h8"/>
                        </svg>
                    </div>

                    <div>
                        <h2>Submitted Student Details</h2>
                        <p>Your admission record has been successfully processed.</p>
                    </div>
                </div>

                {{-- Record Details --}}
                <dl class="detail-grid as-detail-grid">
                    <div class="detail-item as-detail-item">
                        <dt>Record ID</dt>
                        <dd>
                            <code class="as-record-id">{{ $record['record_id'] ?? '—' }}</code>
                        </dd>
                    </div>

                    @foreach ([
                        'admission_number' => 'Admission Number',
                        'first_name' => 'First Name',
                        'last_name' => 'Last Name',
                        'mobile' => 'Mobile'
                    ] as $key => $label)
                        <div class="detail-item as-detail-item">
                            <dt>{{ $label }}</dt>
                            <dd>{{ $record[$key] ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Navigation Actions --}}
                <div class="form-actions as-actions">
                    <a class="btn btn-primary as-btn as-btn-primary"
                       href="{{ route('admission.create', ['class' => $classSlug]) }}">
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Another Student
                    </a>

                    <a class="btn btn-ghost as-btn as-btn-secondary"
                       href="{{ route('admission.select') }}">
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="m15 18-6-6 6-6"/>
                            <path d="M9 12h12"/>
                        </svg>
                        Back to Class Selection
                    </a>
                </div>

                <div class="as-footer-note">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 11v5M12 7h.01"/>
                    </svg>
                    <span>
                        You can add another student to this class or return to class selection.
                    </span>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection