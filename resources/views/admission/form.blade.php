@extends('layouts.app')

@section('title', 'Admission Form · ' . $school->name)

@section('content')
<style>
    .admission-form-page {
        --af-primary: #1b365d;
        --af-primary-dark: #0a1e3d;
        --af-accent: #0284c7;
        --af-text: #1e293b;
        --af-muted: #64748b;
        --af-border: #e2e8f0;

        width: 100%;
        color: var(--af-text);
        font-family: inherit;
    }

    .admission-form-page *,
    .admission-form-page *::before,
    .admission-form-page *::after {
        box-sizing: border-box;
    }

    .admission-form-page .af-card {
        background: #fff;
        border: 1px solid var(--af-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
        margin-bottom: 22px;
    }

    .admission-form-page .af-institution {
        position: relative;
        text-align: center;
        padding: 34px 24px 28px;
    }

    .admission-form-page .af-top-line {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(
            90deg,
            #1e3a8a,
            #0ea5e9,
            #1e3a8a
        );
    }

    /* Logo only: no background, no border, no shadow */
    .admission-form-page .af-crest {
        width: 90px;
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        padding: 0;
        background: none;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .admission-form-page .af-crest-logo {
        display: block;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        background: none;
        border: 0;
        box-shadow: none;
    }

    .admission-form-page .af-institution h1 {
        margin: 0 0 8px;
        color: var(--af-primary-dark);
        font-size: clamp(23px, 4vw, 32px);
        font-weight: 800;
        line-height: 1.4;
    }

    .admission-form-page .af-institution p {
        margin: 0;
        color: var(--af-muted);
        font-size: 14px;
        line-height: 1.8;
    }

    .admission-form-page .af-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 15px;
        padding: 6px 12px;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e40af;
        font-size: 12px;
        font-weight: 700;
    }

    .admission-form-page .af-instructions {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 18px;
        margin-bottom: 22px;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        background: #eff6ff;
        line-height: 1.8;
    }

    .admission-form-page .af-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
    }

    .admission-form-page .af-instructions h2 {
        margin: 0 0 5px;
        color: #1e3a8a;
        font-size: 15px;
        font-weight: 800;
    }

    .admission-form-page .af-instructions p {
        margin: 0;
        color: #334155;
        font-size: 13px;
    }

    .admission-form-page .af-context {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        padding: 17px 20px;
    }

    .admission-form-page .af-context h2 {
        margin: 0 0 5px;
        font-size: 17px;
        font-weight: 800;
    }

    .admission-form-page .af-context p {
        margin: 0;
        color: var(--af-muted);
        font-size: 13px;
    }

    .admission-form-page .af-class-name {
        color: #1d4ed8;
        font-weight: 800;
    }

    .admission-form-page .af-panel-heading {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 22px;
        border-bottom: 1px solid var(--af-border);
        background: #f8fafc;
    }

    .admission-form-page .af-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 43px;
        height: 43px;
        flex-shrink: 0;
        border-radius: 11px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 19px;
    }

    .admission-form-page .af-panel-heading h2 {
        margin: 0 0 5px;
        color: #0f172a;
        font-size: 17px;
        font-weight: 800;
    }

    .admission-form-page .af-panel-heading p {
        margin: 0;
        color: var(--af-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .admission-form-page .af-body {
        padding: 24px;
    }

    .admission-form-page .af-required-note {
        margin: 0 0 23px;
        color: var(--af-muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .admission-form-page .af-required-note span {
        color: #dc2626;
        font-weight: 800;
    }

    .admission-form-page .af-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px 24px;
    }

    .admission-form-page .af-fields > * {
        min-width: 0;
    }

    .admission-form-page .af-fields label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.8;
    }

    .admission-form-page .af-fields input:not([type="checkbox"]):not([type="radio"]),
    .admission-form-page .af-fields select,
    .admission-form-page .af-fields textarea {
        width: 100%;
        max-width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        outline: none;
        background: #fff;
        color: #1e293b;
        font-family: inherit;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
    }

    .admission-form-page .af-fields textarea {
        min-height: 100px;
        resize: vertical;
    }

    .admission-form-page .af-fields input:focus,
    .admission-form-page .af-fields select:focus,
    .admission-form-page .af-fields textarea:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, .15);
    }

    .admission-form-page .af-fields input[type="checkbox"],
    .admission-form-page .af-fields input[type="radio"] {
        accent-color: #1d4ed8;
    }

    .admission-form-page .af-fields input[type="file"] {
        min-height: auto;
        padding: 9px;
        background: #f8fafc;
    }

    .admission-form-page .af-fields .req,
    .admission-form-page .af-fields .required {
        color: #dc2626;
    }

    .admission-form-page .af-fields small,
    .admission-form-page .af-fields .muted {
        color: var(--af-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .admission-form-page .af-bengali {
        margin-left: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 500;
    }

    .admission-form-page .af-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 11px;
        margin-top: 28px;
        padding-top: 21px;
        border-top: 1px solid #e2e8f0;
    }

    .admission-form-page .af-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 9px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        text-decoration: none;
        cursor: pointer;
        transition: background .2s, border-color .2s;
    }

    .admission-form-page .af-btn-primary {
        background: #1b365d;
        color: #fff;
    }

    .admission-form-page .af-btn-primary:hover {
        background: #0a1e3d;
    }

    .admission-form-page .af-btn-secondary {
        border-color: #cbd5e1;
        background: #fff;
        color: #334155;
    }

    .admission-form-page .af-btn-secondary:hover {
        background: #f1f5f9;
    }

    .admission-form-page .af-footer {
        padding: 20px 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.9;
    }

    @media (max-width: 700px) {
        .admission-form-page .af-institution {
            padding: 28px 17px 23px;
        }

        .admission-form-page .af-panel-heading {
            padding: 17px;
        }

        .admission-form-page .af-body {
            padding: 17px;
        }

        .admission-form-page .af-fields {
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
        }

        .admission-form-page .af-actions {
            align-items: stretch;
        }

        .admission-form-page .af-actions .af-btn {
            flex: 1 1 auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admission-form-page *,
        .admission-form-page *::before,
        .admission-form-page *::after {
            transition: none !important;
        }
    }
</style>

<div class="admission-form-page">

    {{-- Institutional identity --}}
    <section class="af-card af-institution">
        <div class="af-top-line"></div>

        <div class="af-crest">
            <img src="{{ $logoUrl }}"
                 alt="{{ $school->name }} logo"
                 class="af-crest-logo"
                 onerror="this.onerror=null;this.src='{{ asset('images/logo.png') }}';">
        </div>

        <h1>{{ $school->name }}</h1>

        <div class="af-pill">
            শিক্ষার্থীর তথ্য সংগ্রহ ফরম
        </div>

        <p style="margin-top:10px;font-size:12px;">
            Student Information Form
        </p>
    </section>

    {{-- Instructions --}}
    <section class="af-instructions">
        <div class="af-info-icon" aria-hidden="true">i</div>

        <div>
            <h2>ফরম পূরণের নির্দেশনা</h2>
            <p>
                প্রিয় অভিভাবক, শিক্ষার্থীর সঠিক তথ্য দিয়ে নিচের ফরমটি পূরণ করুন।
                <strong style="color:#dc2626;">(*)</strong>
                চিহ্নিত ঘরগুলো পূরণ করা বাধ্যতামূলক। জমা দেওয়ার আগে সব তথ্য যাচাই করুন।
            </p>
        </div>
    </section>

    {{-- School context --}}
    <section class="af-card af-context">
        <div>
            <h2>Admission Form</h2>
            <p>
                School:
                <span class="af-class-name">{{ $school->name }}</span>
            </p>
            <p class="muted small" style="margin-bottom: 0;">
                Select the class and section in the form below.
            </p>
        </div>

        @auth
            <a href="{{ route('admin.schools.show', $school) }}"
               class="af-btn af-btn-secondary">
                &larr; Dashboard
            </a>
        @endauth
    </section>

    {{-- Admission form --}}
    <section class="af-card">
        <div class="af-panel-heading">
            <div class="af-panel-icon" aria-hidden="true">&#9786;</div>

            <div>
                <h2>Student Information</h2>
                <p>
                    Fill in the student's details in the fields below.
                    (শিক্ষার্থীর তথ্য নিচে পূরণ করুন)
                </p>
            </div>
        </div>

        <div class="af-body">

            @if (! empty($notice))
                <div class="af-instructions" role="status">
                    <div>
                        <p>{{ $notice }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="af-instructions"
                     role="alert"
                     style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">
                    <div>
                        <strong>অনুগ্রহ করে নিচের ভুলগুলো সংশোধন করুন।</strong>
                        <ul style="margin:8px 0 0 18px;list-style:disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <p class="af-required-note">
                <span>*</span>
                Fields marked with an asterisk are required.
                (তারকা চিহ্নিত ঘরগুলো পূরণ করা আবশ্যক)
            </p>

            <form method="POST"
                  action="{{ route('admission.preview', ['token' => $school->admission_token]) }}">
                @csrf

                <div class="form-grid af-fields">
                    <div class="form-field {{ $errors->has('class_id') ? 'has-error' : '' }}">
                        <label for="admission-class">
                            Class <span class="af-bengali" lang="bn">(শ্রেণি)</span>
                        </label>

                        <select id="admission-class" name="class_id" autocomplete="off">
                            <option value="">— select class —</option>

                            @foreach ($classes as $class)
                                <option
                                    value="{{ $class->id }}"
                                    @selected((string) old('class_id', $record['class_id'] ?? '') === (string) $class->id)
                                >
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('class_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field {{ $errors->has('section_id') ? 'has-error' : '' }}">
                        <label for="admission-section">
                            Section <span class="af-bengali" lang="bn">(শাখা)</span>
                        </label>

                        <select id="admission-section" name="section_id" autocomplete="off">
                            <option value="">— select section —</option>

                            @foreach ($classes as $class)
                                @foreach ($class->sections as $section)
                                    <option
                                        value="{{ $section->id }}"
                                        data-class="{{ $class->id }}"
                                        @selected((string) old('section_id', $record['section_id'] ?? '') === (string) $section->id)
                                    >
                                        {{ $class->name }} - {{ $section->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>

                        @error('section_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    @foreach ($fields as $name => $field)
                        @include('partials.field', [
                            'name' => $name,
                            'field' => $field,
                            'value' => old($name, $record[$name] ?? null),
                            'required' => $required,
                        ])
                    @endforeach
                </div>

                <div class="form-actions af-actions">
                    <button type="submit" class="af-btn af-btn-primary">
                        Preview Admission &rarr;
                    </button>

                    @auth
                        <a href="{{ route('admin.schools.show', $school) }}"
                           class="af-btn af-btn-secondary">
                            Cancel
                        </a>
                    @else
                        <button type="reset" class="af-btn af-btn-secondary">
                            Reset Form
                        </button>
                    @endauth
                </div>
            </form>
        </div>
    </section>

    <footer class="af-footer">
        <div>© {{ date('Y') }} {{ $school->name }}. All rights reserved.</div>
        <div>School Management System Software Powered by Yes Bangladesh.</div>
    </footer>

</div>

{{-- Add Bengali translations without changing submitted field names or values. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const translations = {
        admission_number: 'ভর্তির নম্বর',
        first_name: 'নামের প্রথম অংশ',
        middle_name: 'মধ্যনাম',
        last_name: 'নামের শেষ অংশ',
        full_name: 'পূর্ণ নাম',
        name: 'নাম',
        gender: 'লিঙ্গ',
        date_of_birth: 'জন্ম তারিখ',
        dob: 'জন্ম তারিখ',
        mobile: 'মোবাইল নম্বর',
        phone: 'ফোন নম্বর',
        roll_no: 'রোল নম্বর',
        guardian_email: 'অভিভাবকের ইমেইল',
        student_email: 'শিক্ষার্থীর ইমেইল',
        email: 'ইমেইল',
        admission_date: 'ভর্তির তারিখ',
        blood_group: 'রক্তের গ্রুপ',
        religion: 'ধর্ম',
        father_name: 'পিতার নাম',
        father_phone: 'পিতার ফোন নম্বর',
        father_occupation: 'পিতার পেশা',
        mother_name: 'মাতার নাম',
        mother_phone: 'মাতার ফোন নম্বর',
        mother_occupation: 'মাতার পেশা',
        guardian_name: 'অভিভাবকের নাম',
        guardian_phone: 'অভিভাবকের ফোন নম্বর',
        guardian_relation: 'অভিভাবকের সঙ্গে সম্পর্ক',
        present_address: 'বর্তমান ঠিকানা',
        permanent_address: 'স্থায়ী ঠিকানা',
        current_address: 'বর্তমান ঠিকানা',
        address: 'ঠিকানা',
        village: 'গ্রাম',
        post_office: 'ডাকঘর',
        post_code: 'পোস্ট কোড',
        postal_code: 'পোস্ট কোড',
        upazila: 'উপজেলা',
        thana: 'থানা',
        district: 'জেলা',
        division: 'বিভাগ',
        country: 'দেশ',
        nationality: 'জাতীয়তা',
        birth_certificate: 'জন্ম নিবন্ধন নম্বর',
        birth_certificate_no: 'জন্ম নিবন্ধন নম্বর',
        nid: 'জাতীয় পরিচয়পত্র নম্বর',
        nid_number: 'জাতীয় পরিচয়পত্র নম্বর',
        previous_school: 'পূর্ববর্তী বিদ্যালয়',
        previous_school_name: 'পূর্ববর্তী বিদ্যালয়ের নাম',
        class: 'শ্রেণি',
        section: 'শাখা',
        session: 'শিক্ষাবর্ষ',
        group: 'বিভাগ / গ্রুপ',
        shift: 'শিফট',
        student_photo: 'শিক্ষার্থীর ছবি',
        photo: 'ছবি',
        image: 'ছবি',
        remarks: 'মন্তব্য',
        occupation: 'পেশা',
        relationship: 'সম্পর্ক',
        emergency_contact: 'জরুরি যোগাযোগ নম্বর',
        guardian_occupation: 'অভিভাবকের পেশা',
        bank_name: 'ব্যাংকের নাম',
        bank_account_no: 'ব্যাংক অ্যাকাউন্ট নম্বর',
        bank_account_number: 'ব্যাংক অ্যাকাউন্ট নম্বর',
        account_no: 'অ্যাকাউন্ট নম্বর',
        account_number: 'অ্যাকাউন্ট নম্বর',
        national_identification_no: 'জাতীয় পরিচয়পত্র নম্বর',
        national_identification_number: 'জাতীয় পরিচয়পত্র নম্বর',
        national_id: 'জাতীয় পরিচয়পত্র নম্বর',
        previous_school_details: 'পূর্ববর্তী বিদ্যালয়ের বিবরণ',
        note: 'নোট'
    };

    const container = document.querySelector(
        '.admission-form-page .af-fields'
    );

    if (!container) {
        return;
    }

    container.querySelectorAll('label[for]').forEach(function (label) {
        const targetId = label.getAttribute('for');
        const field = document.getElementById(targetId);

        if (!field || !container.contains(field)) {
            return;
        }

        const fieldName = field.getAttribute('name');

        if (!fieldName) {
            return;
        }

        const normalizedName = fieldName
            .replace(/\[\]$/, '')
            .replace(/\[([^\]]+)\]/g, '.$1')
            .split('.')
            .pop()
            .toLowerCase();

        const bengali = translations[normalizedName];

        if (!bengali || label.querySelector('.af-bengali')) {
            return;
        }

        const span = document.createElement('span');
        span.className = 'af-bengali';
        span.lang = 'bn';
        span.textContent = '(' + bengali + ')';

        label.appendChild(document.createTextNode(' '));
        label.appendChild(span);
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect = document.getElementById('admission-class');
    const sectionSelect = document.getElementById('admission-section');

    if (!classSelect || !sectionSelect) {
        return;
    }

    const sections = Array.from(sectionSelect.options)
        .filter(function (option) {
            return option.value !== '';
        })
        .map(function (option) {
            return {
                value: option.value,
                text: option.textContent,
                classId: option.dataset.class
            };
        });

    function refreshSections() {
        const classId = classSelect.value;
        const current = sectionSelect.value;

        sectionSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '— select section —';
        sectionSelect.appendChild(placeholder);

        sections.forEach(function (section) {
            if (classId !== '' && section.classId !== classId) {
                return;
            }

            const option = document.createElement('option');
            option.value = section.value;
            option.textContent = section.text;
            option.dataset.class = section.classId;
            option.selected = section.value === current;

            sectionSelect.appendChild(option);
        });

        if (sectionSelect.value !== current) {
            sectionSelect.value = '';
        }
    }

    if (classSelect.value === '') {
        const classOptions = Array.from(classSelect.options).filter(function (option) {
            return option.value !== '';
        });

        if (classOptions.length === 1) {
            classSelect.value = classOptions[0].value;
        }
    }

    refreshSections();

    if (sectionSelect.value === '') {
        const sectionOptions = Array.from(sectionSelect.options).filter(function (option) {
            return option.value !== '';
        });

        if (sectionOptions.length === 1) {
            sectionSelect.value = sectionOptions[0].value;
        }
    }

    classSelect.addEventListener('change', refreshSections);
});
</script>
@endsection