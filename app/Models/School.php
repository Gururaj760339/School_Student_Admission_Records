<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class School extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'admission_token',
        'logo_path',
    ];

    protected static function booted(): void
    {
        static::creating(function (School $school) {
            if (blank($school->slug)) {
                $school->slug = static::generateSlug($school->name);
            }

            if (blank($school->admission_token)) {
                $school->admission_token = static::generateToken();
            }
        });
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function admissionUrl(): string
    {
        return route('admission.create', ['token' => $this->admission_token]);
    }

    public function logoUrl(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        return route('schools.logo.image', ['token' => $this->admission_token]);
    }

    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'school';
        }

        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::query()->where('admission_token', $token)->exists());

        return $token;
    }
}
