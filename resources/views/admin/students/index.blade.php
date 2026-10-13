
@extends('layouts.app')

@section('title', 'Manage Students')

@section('content')
<style>
    .manage-students-page {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin: 0 auto;
        padding: 24px;
        box-sizing: border-box;
    }

    .manage-students-page *,
    .manage-students-page *::before,
    .manage-students-page *::after {
        box-sizing: border-box;
    }

    .manage-students-page .ms-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .manage-students-page .ms-heading h1 {
        margin: 0 0 8px;
        color: #172033;
        font-size: 26px;
        font-weight: 700;
    }

    .manage-students-page .ms-heading p {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
    }

    .manage-students-page .ms-head-actions,
    .manage-students-page .ms-filter-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .manage-students-page .ms-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 12px;
        border: 1px solid #d7deea;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }

    .manage-students-page .ms-btn-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .manage-students-page .ms-btn-primary:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .manage-students-page .ms-btn-ghost:hover {
        background: #f1f5f9;
    }

    .manage-students-page .ms-btn-danger {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .manage-students-page .ms-btn-small {
        min-height: 30px;
        padding: 5px 8px;
        font-size: 11px;
    }

    .manage-students-page .ms-error {
        margin-bottom: 20px;
        padding: 14px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fef2f2;
        color: #991b1b;
        overflow-wrap: anywhere;
    }

    /* Search filters */
    .manage-students-page .ms-filter-panel {
        width: 100%;
        margin-bottom: 24px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    .manage-students-page .ms-filter-bar {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr)
                               minmax(0, 1fr) auto;
        align-items: end;
        gap: 14px;
        width: 100%;
    }

    .manage-students-page .ms-filter-field {
        min-width: 0;
    }

    .manage-students-page .ms-filter-field label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .manage-students-page .ms-filter-field input,
    .manage-students-page .ms-filter-field select {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 42px;
        padding: 9px 10px;
        border: 1px solid #d5deea;
        border-radius: 8px;
        background: #fff;
        color: #1e293b;
        font-family: inherit;
        font-size: 13px;
    }

    /* Student Records card */
    .manage-students-page .ms-table-section {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin: 24px 0 0;
        padding: 20px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
    }

    .manage-students-page .ms-table-heading {
        margin-bottom: 18px;
    }

    .manage-students-page .ms-table-heading h2 {
        margin: 0;
        color: #172033;
        font-size: 18px;
        font-weight: 700;
    }

    /* No horizontal scrolling */
    .manage-students-page .ms-table-scroll {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: visible;
    }

    .manage-students-page .ms-data-table {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        table-layout: fixed;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
    }

    /* Column widths add up to 100% */
    .manage-students-page .ms-data-table th:nth-child(1) { width: 9%; }
    .manage-students-page .ms-data-table th:nth-child(2) { width: 13%; }
    .manage-students-page .ms-data-table th:nth-child(3) { width: 7%; }
    .manage-students-page .ms-data-table th:nth-child(4) { width: 11%; }
    .manage-students-page .ms-data-table th:nth-child(5) { width: 12%; }
    .manage-students-page .ms-data-table th:nth-child(6) { width: 11%; }
    .manage-students-page .ms-data-table th:nth-child(7) { width: 7%; }
    .manage-students-page .ms-data-table th:nth-child(8) { width: 12%; }
    .manage-students-page .ms-data-table th:nth-child(9) { width: 18%; }

    .manage-students-page .ms-data-table thead th {
        padding: 12px 6px;
        border-bottom: 1px solid #e2e8f0;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        overflow-wrap: anywhere;
    }

    .manage-students-page .ms-data-table tbody td {
        padding: 12px 6px;
        border-bottom: 1px solid #edf1f6;
        color: #334155;
        font-size: 11px;
        line-height: 1.5;
        vertical-align: middle;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .manage-students-page .ms-data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .manage-students-page .ms-data-table tbody tr:hover {
        background: #f8fafc;
    }

    .manage-students-page .ms-class-badge {
        display: inline-block;
        max-width: 100%;
        padding: 4px 6px;
        border: 1px solid #bfdbfe;
        border-radius: 20px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .manage-students-page .ms-row-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 3px;
        min-width: 0;
    }

    .manage-students-page .ms-row-actions form {
        display: inline-flex;
        margin: 0;
    }

    .manage-students-page .ms-muted {
        color: #64748b;
    }

    .manage-students-page .ms-small {
        font-size: 11px;
    }

    /* Empty state */
    .manage-students-page .ms-empty-state {
        padding: 36px 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        text-align: center;
    }

    .manage-students-page .ms-empty-state h2 {
        margin: 0 0 10px;
        color: #334155;
        font-size: 18px;
    }

    .manage-students-page .ms-empty-state p {
        margin: 6px 0;
        font-size: 13px;
    }

    .manage-students-page .ms-empty-state a {
        color: #2563eb;
        font-weight: 600;
        text-decoration: none;
    }

    /* Pagination */
    .manage-students-page .ms-pagination-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        width: 100%;
        margin-top: 20px;
    }

    .manage-students-page .ms-pagination-wrap p {
        margin: 0;
        font-size: 12px;
    }

    .manage-students-page .ms-pagination-wrap nav {
        max-width: 100%;
    }

    /* Tablet: filter fields wrap, table stays within page */
    @media (max-width: 900px) {
        .manage-students-page .ms-filter-bar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Mobile: records become readable stacked cards */
    @media (max-width: 640px) {
        .manage-students-page {
            padding: 12px;
        }

        .manage-students-page .ms-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .manage-students-page .ms-heading h1 {
            font-size: 22px;
        }

        .manage-students-page .ms-head-actions {
            width: 100%;
        }

        .manage-students-page .ms-filter-panel {
            padding: 14px;
        }

        .manage-students-page .ms-filter-bar {
            grid-template-columns: minmax(0, 1fr);
        }

        .manage-students-page .ms-filter-actions {
            width: 100%;
        }

        .manage-students-page .ms-filter-actions .ms-btn {
            flex: 1;
        }

        .manage-students-page .ms-table-section {
            padding: 12px;
        }

        .manage-students-page .ms-table-scroll {
            overflow: visible;
        }

        .manage-students-page .ms-data-table,
        .manage-students-page .ms-data-table tbody {
            display: block;
            width: 100%;
        }

        .manage-students-page .ms-data-table thead {
            display: none;
        }

        .manage-students-page .ms-data-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0 12px;
            margin-bottom: 12px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
        }

        .manage-students-page .ms-data-table tbody tr:last-child {
            margin-bottom: 0;
        }

        .manage-students-page .ms-data-table tbody td {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 10px 0;
            border-bottom: 1px solid #edf1f6;
            font-size: 12px;
        }

        .manage-students-page .ms-data-table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 5px;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
        }

        .manage-students-page .ms-data-table tbody td:last-child {
            grid-column: 1 / -1;
            border-bottom: none;
        }

        .manage-students-page .ms-row-actions {
            gap: 6px;
        }

        .manage-students-page .ms-pagination-wrap {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 380px) {
        .manage-students-page .ms-data-table tbody tr {
            grid-template-columns: minmax(0, 1fr);
        }

        .manage-students-page .ms-data-table tbody td:last-child {
            grid-column: auto;
        }

        .manage-students-page .ms-head-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .manage-students-page .ms-head-actions .ms-btn {
            width: 100%;
        }
    }
</style>

<div class="card manage-students-page">
    <div class="ms-header">
        <div class="ms-heading">
            <h1>Manage Students</h1>
            <p class="muted">Records are read live from Google Sheets.</p>
        </div>

        <div class="ms-head-actions">
            <a class="ms-btn ms-btn-ghost"
               href="{{ route('admin.classes.index') }}">
                Classes &amp; Sections
            </a>

            <a class="ms-btn ms-btn-primary"
               href="{{ route('admission.select') }}">
                + New Admission
            </a>
        </div>
    </div>

    @if ($serviceError)
        <div class="ms-error" role="alert">
            {{ $serviceError }}
        </div>
    @endif

    <div class="ms-filter-panel">
        <form method="GET"
              action="{{ route('admin.students.index') }}"
              class="ms-filter-bar">

            <div class="ms-filter-field">
                <label for="search">Search</label>
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Admission no, name, mobile, roll no">
            </div>

            <div class="ms-filter-field">
                <label for="class">Class</label>
                <select id="class" name="class">
                    <option value="all">All classes</option>
                    @foreach ($classes as $slug => $name)
                        <option value="{{ $slug }}" @selected($classFilter === $slug)>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ms-filter-field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">All</option>
                    @foreach (['Male', 'Female', 'Other'] as $option)
                        <option value="{{ $option }}" @selected($gender === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ms-filter-actions">
                <button type="submit" class="ms-btn ms-btn-primary">
                    Apply
                </button>

                <a class="ms-btn ms-btn-ghost"
                   href="{{ route('admin.students.index') }}">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if (! $serviceError && $students->isEmpty())
        <div class="ms-empty-state">
            <h2>No Student Records Found</h2>
            <p class="muted">There are no student records matching your current filters.</p>
            <p class="muted">
                Adjust the filters, or
                <a href="{{ route('admission.select') }}">add a new admission</a>.
            </p>
        </div>
    @endif

    @if (! $serviceError && $students->isNotEmpty())
        <section class="ms-table-section">
            <div class="ms-table-heading">
                <h2>Student Records</h2>
            </div>

            <div class="ms-table-scroll">
                <table class="ms-data-table">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Mobile</th>
                            <th>Class</th>
                            <th>Roll No</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td data-label="Admission No">
                                    {{ $student['admission_number'] ?: '—' }}
                                </td>

                                <td data-label="Student Name">
                                    {{ trim(($student['first_name'] ?? '').' '.($student['last_name'] ?? '')) ?: '—' }}
                                </td>

                                <td data-label="Gender">
                                    {{ $student['gender'] ?: '—' }}
                                </td>

                                <td data-label="Date of Birth">
                                    {{ $student['date_of_birth'] ?: '—' }}
                                </td>

                                <td data-label="Mobile">
                                    {{ $student['mobile'] ?: '—' }}
                                </td>

                                <td data-label="Class">
                                    <span class="ms-class-badge">
                                        {{ $student['_class_name'] ?? '—' }}
                                    </span>
                                </td>

                                <td data-label="Roll No">
                                    {{ $student['roll_no'] ?: '—' }}
                                </td>

                                <td data-label="Email">
                                    {{ $student['email'] ?: '—' }}
                                </td>

                                <td data-label="Actions">
                                    <div class="ms-row-actions">
                                        @if (! empty($student['record_id']))
                                            <a
                                                class="ms-btn ms-btn-small ms-btn-ghost"
                                                href="{{ route('admin.students.show', ['class' => $student['_class_slug'], 'recordId' => $student['record_id']]) }}">
                                                View
                                            </a>

                                            <a
                                                class="ms-btn ms-btn-small ms-btn-ghost"
                                                href="{{ route('admin.students.edit', ['class' => $student['_class_slug'], 'recordId' => $student['record_id']]) }}">
                                                Edit
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('admin.students.destroy', ['class' => $student['_class_slug'], 'recordId' => $student['record_id']]) }}"
                                                data-confirm="Delete this student record? This action cannot be undone.">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="ms-btn ms-btn-small ms-btn-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        @else
                                            <span class="ms-muted ms-small">
                                                Missing record_id
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ms-pagination-wrap">
                <p class="muted small">
                    Showing {{ $students->firstItem() }}–{{ $students->lastItem() }}
                    of {{ $students->total() }} records.
                </p>

                {{ $students->links('partials.pagination') }}
            </div>
        </section>
    @endif
</div>
@endsection
