
@extends('layouts.app')

@section('title', 'Student · '.$className)

@section('content')
<style>
    .student-record-page {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin: 0 auto;
        padding: 24px;
        box-sizing: border-box;
        overflow: hidden;
        color: #1e293b;
    }

    .student-record-page *,
    .student-record-page *::before,
    .student-record-page *::after {
        box-sizing: border-box;
    }

    /* Header */
    .student-record-page .sr-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 18px;
        padding-bottom: 22px;
        margin-bottom: 24px;
        border-bottom: 1px solid #e8edf4;
    }

    .student-record-page .sr-heading {
        min-width: 0;
    }

    .student-record-page .sr-heading h1 {
        margin: 0 0 10px;
        color: #172033;
        font-size: 26px;
        font-weight: 750;
        line-height: 1.35;
        letter-spacing: -.4px;
    }

    .student-record-page .sr-subtitle {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
    }

    .student-record-page .sr-class-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border: 1px solid #bfdbfe;
        border-radius: 20px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 600;
    }

    .student-record-page .sr-record-id {
        display: inline-block;
        max-width: 100%;
        padding: 4px 9px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .student-record-page .sr-dot {
        color: #cbd5e1;
    }

    /* Buttons */
    .student-record-page .sr-header-actions,
    .student-record-page .sr-footer-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-record-page .sr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px;
        padding: 9px 15px;
        border: 1px solid #d8e0eb;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
        text-align: center;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: background .2s ease, border-color .2s ease;
    }

    .student-record-page .sr-btn:hover {
        background: #f8fafc;
        border-color: #b8c5d6;
        color: #172033;
    }

    .student-record-page .sr-btn-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .student-record-page .sr-btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .student-record-page .sr-btn-danger {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .student-record-page .sr-btn-danger:hover {
        border-color: #fda4af;
        background: #ffe4e6;
        color: #9f1239;
    }

    /* Section heading */
    .student-record-page .sr-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 18px;
    }

    .student-record-page .sr-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 18px;
    }

    .student-record-page .sr-section-heading h2 {
        margin: 0 0 4px;
        color: #172033;
        font-size: 17px;
        font-weight: 700;
    }

    .student-record-page .sr-section-heading p {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    /* Student information grid */
    .student-record-page .sr-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        width: 100%;
        margin: 0;
    }

    .student-record-page .sr-detail-item {
        min-width: 0;
        min-height: 86px;
        padding: 15px 16px;
        border: 1px solid #e5eaf2;
        border-radius: 10px;
        background: #fff;
        transition: border-color .2s ease, background .2s ease;
    }

    .student-record-page .sr-detail-item:hover {
        border-color: #cbdaf0;
        background: #fcfdff;
    }

    .student-record-page .sr-detail-item dt {
        margin: 0 0 9px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .student-record-page .sr-detail-item dd {
        margin: 0;
        color: #1e293b;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.6;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .student-record-page .sr-empty-value {
        color: #94a3b8;
        font-weight: 400;
    }

    /* Footer actions */
    .student-record-page .sr-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 26px;
        padding-top: 20px;
        border-top: 1px solid #e8edf4;
    }

    .student-record-page .sr-footer-note {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
    }

    .student-record-page .sr-footer form {
        display: inline-flex;
        margin: 0;
    }

    /* Tablet */
    @media (max-width: 900px) {
        .student-record-page .sr-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Mobile */
    @media (max-width: 600px) {
        .student-record-page {
            padding: 15px;
        }

        .student-record-page .sr-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 16px;
        }

        .student-record-page .sr-heading h1 {
            font-size: 23px;
        }

        .student-record-page .sr-header-actions {
            width: 100%;
        }

        .student-record-page .sr-header-actions .sr-btn {
            flex: 1;
        }

        .student-record-page .sr-detail-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
        }

        .student-record-page .sr-detail-item {
            min-height: auto;
            padding: 13px 14px;
        }

        .student-record-page .sr-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .student-record-page .sr-footer-actions {
            width: 100%;
        }

        .student-record-page .sr-footer-actions > a,
        .student-record-page .sr-footer-actions > form,
        .student-record-page .sr-footer-actions > form .sr-btn {
            flex: 1;
        }

        .student-record-page .sr-footer-actions > form .sr-btn {
            width: 100%;
        }
    }

    @media (max-width: 380px) {
        .student-record-page {
            padding: 10px;
        }

        .student-record-page .sr-header-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .student-record-page .sr-footer-actions {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<div class="card student-record-page">
    <div class="sr-header">
        <div class="sr-heading">
            <h1>Student Record</h1>

            <p class="sr-subtitle">
                <span>Class:</span>
                <span class="sr-class-badge">{{ $className }}</span>

                <span class="sr-dot">·</span>

                <span>Record ID:</span>
                <code class="sr-record-id">{{ $record['record_id'] ?? '—' }}</code>
            </p>
        </div>

        <div class="sr-header-actions">
            <a class="sr-btn"
               href="{{ route('admin.students.index', ['class' => $classSlug]) }}">
                &larr; Back
            </a>

            @if (! empty($record['record_id']))
                <a class="sr-btn sr-btn-primary"
                   href="{{ route('admin.students.edit', ['class' => $classSlug, 'recordId' => $record['record_id']]) }}">
                    Edit Record
                </a>
            @endif
        </div>
    </div>

    <div class="sr-section-heading">
        <span class="sr-section-icon" aria-hidden="true">&#128100;</span>

        <div>
            <h2>Student Information</h2>
            <p>Review the details saved for this student.</p>
        </div>
    </div>

    <dl class="detail-grid sr-detail-grid">
        @foreach ($fields as $name => $field)
            <div class="detail-item sr-detail-item">
                <dt>{{ $field['label'] ?? $name }}</dt>

                <dd>
                    @if (($record[$name] ?? null) === null || $record[$name] === '')
                        <span class="sr-empty-value">—</span>
                    @else
                        {{ $record[$name] }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="sr-footer">
        <p class="sr-footer-note">
            Student record details
        </p>

        <div class="sr-footer-actions">
            <a class="sr-btn"
               href="{{ route('admin.students.index', ['class' => $classSlug]) }}">
                Back to List
            </a>

            @if (! empty($record['record_id']))
                <form method="POST"
                      action="{{ route('admin.students.destroy', ['class' => $classSlug, 'recordId' => $record['record_id']]) }}"
                      data-confirm="Delete this student record? This action cannot be undone.">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="sr-btn sr-btn-danger">
                        Delete Record
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
