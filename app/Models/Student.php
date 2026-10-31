<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'school_id',
        'class_id',
        'section_id',
        'submission_token',
        'admission_number',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'mobile',
        'roll_no',
        'guardian_email',
        'email',
        'admission_date',
        'blood_group',
        'religion',
        'height',
        'weight',
        'father_name',
        'father_phone',
        'father_occupation',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'guardian_name',
        'guardian_relation',
        'guardian_phone',
        'guardian_occupation',
        'current_address',
        'bank_account_no',
        'bank_name',
        'national_identification_no',
        'previous_school_details',
        'note',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
