
@extends('layouts.app')

@section('title', 'Edit Student · '.($className ?? 'Record'))

@section('content')
<style>
    .edit-student-page {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin: 0 auto;
        padding: 24px;
        box-sizing: border-box;
        color: #1e293b;
    }

    .edit-student-page *,
    .edit-student-page *::before,
    .edit-student-page *::after {
        box-sizing: border-box;
    }

    /* Header */
    .edit-student-page .es-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 24px;
        padding-bottom: 22px;
        border-bottom: 1px solid #e8edf4;
    }

    .edit-student-page .es-heading h1 {
        margin: 0 0 10px;
        color: #172033;
        font-size: 26px;
        font-weight: 750;
        line-height: 1.35;
    }

    .edit-student-page .es-subtitle {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
    }

    .edit-student-page .es-class-badge {
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

    .edit-student-page .es-record-id {
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

    /* Buttons */
    .edit-student-page .es-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px;
        padding: 9px 16px;
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

    .edit-student-page .es-btn:hover {
        background: #f8fafc;
        border-color: #b8c5d6;
        color: #172033;
    }

    .edit-student-page .es-btn-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .edit-student-page .es-btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    /* Form section heading */
    .edit-student-page .es-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .edit-student-page .es-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 19px;
    }

    .edit-student-page .es-section-heading h2 {
        margin: 0 0 4px;
        color: #172033;
        font-size: 17px;
        font-weight: 700;
    }

    .edit-student-page .es-section-heading p {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
    }

    /* Form layout */
    .edit-student-page .es-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        width: 100%;
    }

    .edit-student-page .es-form-grid > * {
        min-width: 0;
    }

    /* Style existing partial field controls without changing their logic */
    .edit-student-page .es-form-grid label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .edit-student-page .es-form-grid input:not([type="checkbox"]):not([type="radio"]),
    .edit-student-page .es-form-grid select,
    .edit-student-page .es-form-grid textarea {
        display: block;
        width: 100%;
        max-width: 100%;
        min-height: 43px;
        padding: 10px 12px;
        border: 1px solid #d5deea;
        border-radius: 8px;
        outline: none;
        background: #fff;
        color: #1e293b;
        font-family: inherit;
        font-size: 13px;
        line-height: 1.5;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .edit-student-page .es-form-grid textarea {
        min-height: 100px;
        resize: vertical;
    }

    .edit-student-page .es-form-grid input:focus,
    .edit-student-page .es-form-grid select:focus,
    .edit-student-page .es-form-grid textarea:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    .edit-student-page .es-form-grid input[type="checkbox"],
    .edit-student-page .es-form-grid input[type="radio"] {
        accent-color: #2563eb;
    }

    .edit-student-page .es-form-grid .text-danger,
    .edit-student-page .es-form-grid .invalid-feedback {
        color: #dc2626;
        font-size: 12px;
    }

    /* Validation error */
    .edit-student-page .es-error {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 22px;
        padding: 14px 16px;
        border: 1px solid #fecaca;
        border-radius: 10px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 13px;
        line-height: 1.7;
    }

    /* Form footer */
    .edit-student-page .es-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid #e8edf4;
    }

    .edit-student-page .es-footer-note {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .edit-student-page .es-form-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* Responsive */
    @media (max-width: 700px) {
        .edit-student-page {
            padding: 16px;
        }

        .edit-student-page .es-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-student-page .es-heading h1 {
            font-size: 23px;
        }

        .edit-student-page .es-form-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 16px;
        }

        .edit-student-page .es-form-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-student-page .es-form-actions {
            width: 100%;
        }

        .edit-student-page .es-form-actions .es-btn {
            flex: 1;
        }
    }

    @media (max-width: 380px) {
        .edit-student-page {
            padding: 10px;
        }

        .edit-student-page .es-form-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .edit-student-page .es-form-actions .es-btn {
            width: 100%;
        }
    }
</style>

<div class="card edit-student-page">
    <div class="es-header">
        <div class="es-heading">
            <h1>Edit Student</h1>

            <p class="es-subtitle">
                <span>{{ $school->name }}</span>

                <span aria-hidden="true">·</span>

                <span>Class:</span>
                <span class="es-class-badge">
                    {{ $className ?? '—' }}@if ($sectionName) · {{ $sectionName }}@endif
                </span>

                <span aria-hidden="true">·</span>

                <span>Record ID:</span>
                <code class="es-record-id">{{ $record['record_id'] ?? '—' }}</code>
            </p>
        </div>

        <a class="es-btn"
           href="{{ route('admin.students.index', $school) }}">
            &larr; Back to Students
        </a>
    </div>

    @if ($errors->any())
        <div class="es-error" role="alert">
            <span aria-hidden="true">!</span>
            <div>Please correct the highlighted fields and save again.</div>
        </div>
    @endif

    <div class="es-section-heading">
        <span class="es-section-icon" aria-hidden="true">&#9998;</span>

        <div>
            <h2>Student Information</h2>
            <p>Update the student details below and save your changes.</p>
        </div>
    </div>

    <form method="POST"
          action="{{ route('admin.students.update', [$school, $student]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid es-form-grid">
            @foreach ($fields as $name => $field)
                @include('partials.field', [
                    'name' => $name,
                    'field' => $field,
                    'value' => $record[$name] ?? null,
                    'required' => $required,
                ])
            @endforeach
        </div>

        <div class="es-form-footer">
            <p class="es-footer-note">
                {{ $school->name }} · Please review the details before saving.
            </p>

            <div class="es-form-actions">
                <button type="submit" class="es-btn es-btn-primary">
                    Save Changes
                </button>

                <a class="es-btn"
                   href="{{ route('admin.students.index', $school) }}">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const translations = {
        admission_number: 'ভর্তির নম্বর',
        first_name: 'নামের প্রথম অংশ',
        middle_name: 'নামের মধ্যবর্তী অংশ',
        last_name: 'নামের শেষ অংশ',
        gender: 'লিঙ্গ',
        date_of_birth: 'জন্ম তারিখ',
        mobile: 'মোবাইল নম্বর',
        phone: 'ফোন নম্বর',
        email: 'ইমেইল',
        roll_no: 'রোল নম্বর',
        class: 'শ্রেণি',
        section: 'শাখা',
        admission_date: 'ভর্তির তারিখ',
        blood_group: 'রক্তের গ্রুপ',
        religion: 'ধর্ম',
        height: 'উচ্চতা',
        weight: 'ওজন',
        father_name: 'বাবার নাম',
        father_phone: 'বাবার মোবাইল নম্বর',
        father_occupation: 'বাবার পেশা',
        mother_name: 'মায়ের নাম',
        mother_phone: 'মায়ের মোবাইল নম্বর',
        mother_occupation: 'মায়ের পেশা',
        guardian_name: 'অভিভাবকের নাম',
        guardian_phone: 'অভিভাবকের মোবাইল নম্বর',
        guardian_email: 'অভিভাবকের ইমেইল',
        guardian_occupation: 'অভিভাবকের পেশা',
        current_address: 'বর্তমান ঠিকানা',
        present_address: 'বর্তমান ঠিকানা',
        permanent_address: 'স্থায়ী ঠিকানা',
        village: 'গ্রাম',
        post_office: 'ডাকঘর',
        thana: 'থানা',
        district: 'জেলা',
        postal_code: 'পোস্ট কোড',
        bank_account_no: 'ব্যাংক অ্যাকাউন্ট নম্বর',
        bank_account_number: 'ব্যাংক অ্যাকাউন্ট নম্বর',
        bank_name: 'ব্যাংকের নাম',
        national_identification_no: 'জাতীয় পরিচয়পত্র নম্বর',
        nid: 'জাতীয় পরিচয়পত্র নম্বর',
        previous_school: 'পূর্ববর্তী বিদ্যালয়',
        previous_school_details: 'পূর্ববর্তী বিদ্যালয়ের বিবরণ',
        note: 'মন্তব্য',
        remarks: 'মন্তব্য',
        bloodgroup: 'রক্তের গ্রুপ',
        student_name: 'শিক্ষার্থীর নাম',
        name: 'নাম',
        dob: 'জন্ম তারিখ',
        address: 'ঠিকানা',
        emergency_contact: 'জরুরি যোগাযোগ নম্বর',
        emergency_contact_name: 'জরুরি যোগাযোগের ব্যক্তির নাম'
    };

    const normalize = function (value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[\s-]+/g, '_');
    };

    const root = document.querySelector('.edit-student-page');

    if (!root) {
        return;
    }

    root.querySelectorAll('.es-form-grid label').forEach(function (label) {
        if (label.dataset.bengaliAdded === 'true') {
            return;
        }

        const targetId = label.getAttribute('for');
        let input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            input = label.querySelector('input, select, textarea');
        }

        if (!input) {
            return;
        }

        const fieldName = normalize(
            input.name
                ? input.name.replace(/\[\]$/, '').replace(/^.*\[/, '').replace(/\]$/, '')
                : input.id
        );

        const bengali = translations[fieldName];

        if (!bengali) {
            return;
        }

        const labelText = (label.textContent || '').trim();

        if (!labelText || labelText.includes('(' + bengali + ')')) {
            return;
        }

        const suffix = document.createElement('span');
        suffix.className = 'es-bengali-label';
        suffix.textContent = ' (' + bengali + ')';
        suffix.style.fontWeight = '500';
        suffix.style.color = '#64748b';

        label.appendChild(suffix);
        label.dataset.bengaliAdded = 'true';
    });
});
</script>
@endsection
