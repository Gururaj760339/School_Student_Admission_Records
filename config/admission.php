<?php

/*
|--------------------------------------------------------------------------
| Admission Fields
|--------------------------------------------------------------------------
|
| The 30 student fields, in the exact order used for the Google Sheets
| header row. This array is the single source of truth for:
|   - server-side validation rules
|   - form rendering and field labels
|   - Google Sheets column order
|
| "record_id" is a technical column appended after these 30 fields and is
| managed entirely by the application (never submitted by the user).
|
*/

return [

    // Exactly these fields are required; every other field is nullable.
    'required_fields' => [
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'mobile',
    ],

    'fields' => [

        'admission_number' => [
            'label' => 'Admission Number',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:50'],
        ],

        'first_name' => [
            'label' => 'First Name',
            'type' => 'text',
            'rules' => ['required', 'string', 'max:100'],
        ],

        'last_name' => [
            'label' => 'Last Name',
            'type' => 'text',
            'rules' => ['required', 'string', 'max:100'],
        ],

        'gender' => [
            'label' => 'Gender',
            'type' => 'select',
            'options' => ['Male', 'Female', 'Other'],
            'rules' => ['required', 'in:Male,Female,Other'],
        ],

        'date_of_birth' => [
            'label' => 'Date of Birth',
            'type' => 'date',
            'rules' => ['required', 'date', 'before_or_equal:today'],
        ],

        'mobile' => [
            'label' => 'Mobile',
            'type' => 'tel',
            'rules' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{6,30}$/'],
        ],

        'roll_no' => [
            'label' => 'Roll No',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:50'],
        ],

        'guardian_email' => [
            'label' => 'Guardian Email',
            'type' => 'email',
            'rules' => ['nullable', 'email', 'max:255'],
        ],

        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'rules' => ['nullable', 'email', 'max:255'],
        ],

        'admission_date' => [
            'label' => 'Admission Date',
            'type' => 'date',
            'rules' => ['nullable', 'date'],
        ],

        'blood_group' => [
            'label' => 'Blood Group',
            'type' => 'select',
            'options' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'],
            'rules' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown'],
        ],

        'religion' => [
            'label' => 'Religion',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:100'],
        ],

        'height' => [
            'label' => 'Height (cm)',
            'type' => 'number',
            'step' => '0.01',
            'rules' => ['nullable', 'numeric', 'between:0,300'],
        ],

        'weight' => [
            'label' => 'Weight (kg)',
            'type' => 'number',
            'step' => '0.01',
            'rules' => ['nullable', 'numeric', 'between:0,1000'],
        ],

        'father_name' => [
            'label' => 'Father Name',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'father_phone' => [
            'label' => 'Father Phone',
            'type' => 'tel',
            'rules' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{6,30}$/'],
        ],

        'father_occupation' => [
            'label' => 'Father Occupation',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'mother_name' => [
            'label' => 'Mother Name',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'mother_phone' => [
            'label' => 'Mother Phone',
            'type' => 'tel',
            'rules' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{6,30}$/'],
        ],

        'mother_occupation' => [
            'label' => 'Mother Occupation',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'guardian_name' => [
            'label' => 'Guardian Name',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'guardian_relation' => [
            'label' => 'Guardian Relation',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:100'],
        ],

        'guardian_phone' => [
            'label' => 'Guardian Phone',
            'type' => 'tel',
            'rules' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{6,30}$/'],
        ],

        'guardian_occupation' => [
            'label' => 'Guardian Occupation',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'current_address' => [
            'label' => 'Current Address',
            'type' => 'textarea',
            'rules' => ['nullable', 'string', 'max:1000'],
        ],

        'bank_account_no' => [
            'label' => 'Bank Account No',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:50'],
        ],

        'bank_name' => [
            'label' => 'Bank Name',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:150'],
        ],

        'national_identification_no' => [
            'label' => 'National Identification No',
            'type' => 'text',
            'rules' => ['nullable', 'string', 'max:60'],
        ],

        'previous_school_details' => [
            'label' => 'Previous School Details',
            'type' => 'textarea',
            'rules' => ['nullable', 'string', 'max:2000'],
        ],

        'note' => [
            'label' => 'Note',
            'type' => 'textarea',
            'rules' => ['nullable', 'string', 'max:2000'],
        ],

    ],

];
