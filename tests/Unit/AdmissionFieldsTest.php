<?php

namespace Tests\Unit;

use App\Support\AdmissionClasses;
use App\Support\AdmissionFields;
use Tests\TestCase;

class AdmissionFieldsTest extends TestCase
{
    public function test_headers_are_exactly_the_thirty_fields_in_order(): void
    {
        $expected = [
            'admission_number', 'first_name', 'last_name', 'gender', 'date_of_birth',
            'mobile', 'roll_no', 'guardian_email', 'email', 'admission_date',
            'blood_group', 'religion', 'height', 'weight', 'father_name',
            'father_phone', 'father_occupation', 'mother_name', 'mother_phone', 'mother_occupation',
            'guardian_name', 'guardian_relation', 'guardian_phone', 'guardian_occupation', 'current_address',
            'bank_account_no', 'bank_name', 'national_identification_no', 'previous_school_details', 'note',
        ];

        $this->assertSame($expected, AdmissionFields::headers());
        $this->assertCount(30, AdmissionFields::headers());
        $this->assertNotContains('record_id', AdmissionFields::headers());
    }

    public function test_only_the_five_required_fields_are_required(): void
    {
        $this->assertSame(
            ['first_name', 'last_name', 'gender', 'date_of_birth', 'mobile'],
            AdmissionFields::required()
        );
    }

    public function test_optional_fields_are_nullable(): void
    {
        foreach (AdmissionFields::names() as $name) {
            if (AdmissionFields::isRequired($name)) {
                continue;
            }

            $this->assertContains('nullable', AdmissionFields::rules()[$name], "{$name} should be nullable.");
        }
    }

    public function test_classes_map_to_distinct_worksheets(): void
    {
        $this->assertTrue(AdmissionClasses::exists('class_1'));
        $this->assertSame('Class 1', AdmissionClasses::worksheetFor('class_1'));
        $this->assertSame('Class 2', AdmissionClasses::worksheetFor('class_2'));
        $this->assertFalse(AdmissionClasses::exists('class_3'));
    }

    public function test_normalize_preserves_leading_zeros_and_trims(): void
    {
        $normalized = AdmissionFields::normalize([
            'admission_number' => ' 000123 ',
            'first_name' => 'Asha',
            'unknown_field' => 'ignored',
        ]);

        $this->assertSame('000123', $normalized['admission_number']);
        $this->assertSame('Asha', $normalized['first_name']);
        $this->assertNull($normalized['mobile']);
        $this->assertArrayNotHasKey('unknown_field', $normalized);
    }
}
